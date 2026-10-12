<?php
declare(strict_types=1);
require_once __DIR__ . '/message-transport.php';
require_once __DIR__ . '/booking-reliability.php';

function notificationSettings(PDO $pdo): array
{
    return $pdo->query('SELECT * FROM notification_settings WHERE id=1')->fetch() ?: [
        'email_enabled'=>1,'sms_enabled'=>0,'booking_confirmation_enabled'=>1,'reminder_minutes'=>30,'sms_sender_name'=>'NBW','reply_to_email'=>''];
}
function notificationPreference(PDO $pdo, int $userId, string $key): bool
{
    $q=$pdo->prepare('SELECT enabled FROM account_notification_preferences WHERE user_id=? AND notification_key=?');
    $q->execute([$userId,$key]);$value=$q->fetchColumn();return $value===false || (int)$value===1;
}
function notificationChannels(array $settings, bool $recovery = false): array
{
    if ($recovery) return ['email'];
    $channels=[];
    if (!empty($settings['email_enabled'])) $channels[]='email';
    if (!empty($settings['sms_enabled']) && messageConfig()['sms_provider_enabled']) $channels[]='sms';
    return $channels;
}
function notificationPrepare(PDO $pdo): void
{
    // Release unpaid expired holds only after acquiring the same booking mutex.
    bookingWriteBegin($pdo);
    try {
        $pdo->exec("UPDATE bookings b SET b.status='cancelled',b.updated_at=NOW() WHERE b.status='pending' AND b.payment_due_at IS NOT NULL AND b.payment_due_at<NOW() AND NOT EXISTS(SELECT 1 FROM payments p WHERE p.booking_id=b.id AND p.status IN ('pending','verified'))");
        $pdo->commit();
    } catch(Throwable $e) {if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    $settings=notificationSettings($pdo);
    $minutes=max(1,min(10080,(int)$settings['reminder_minutes']));
    // Unique schedule key prevents duplicate reminders on overlapping worker runs.
    $pdo->prepare("INSERT IGNORE INTO appointment_events(event_key,event_type,booking_id,booking_date,booking_time,booking_status,staff_id)
        SELECT CONCAT('reminder:',id,':',booking_date,':',booking_time,':',COALESCE(staff_id,0)), 'reminder',id,booking_date,booking_time,status,staff_id
        FROM bookings WHERE status='confirmed' AND TIMESTAMP(booking_date,booking_time)>NOW()
        AND TIMESTAMP(booking_date,booking_time)<=DATE_ADD(NOW(),INTERVAL ? MINUTE)")->execute([$minutes]);
    $events=$pdo->query('SELECT * FROM appointment_events WHERE expanded_at IS NULL ORDER BY id LIMIT 200')->fetchAll();
    foreach ($events as $event) {
        $pdo->beginTransaction();
        try {
            $recipients=[];
            if ($event['event_type']==='password_reset') {
                $recipients[(int)$event['user_id']]='security';
            } else {
                $q=$pdo->prepare('SELECT customer_id,staff_id FROM bookings WHERE id=?');$q->execute([$event['booking_id']]);$b=$q->fetch();
                if ($b) {
                    $reminder=$event['event_type']==='reminder';
                    $recipients[(int)$b['customer_id']]=$reminder?'reminder_alerts':'booking_alerts';
                    if ($event['staff_id']) $recipients[(int)$event['staff_id']]=$reminder?'reminder_alerts':'assignment_alerts';
                    if (!$reminder && $event['previous_staff_id'] && $event['previous_staff_id']!=$event['staff_id']) $recipients[(int)$event['previous_staff_id']]='schedule_alerts';
                    if (!$reminder) {
                        foreach($pdo->query("SELECT id FROM users WHERE role='admin' AND status='active'")->fetchAll(PDO::FETCH_COLUMN) as $id) $recipients[(int)$id]='booking_alerts';
                    }
                }
            }
            foreach($recipients as $id=>$preference) {
                foreach(notificationChannels($settings,$preference==='security') as $channel) {
                    $pdo->prepare('INSERT IGNORE INTO notification_deliveries(event_id,user_id,channel,preference_key) VALUES(?,?,?,?)')->execute([$event['id'],$id,$channel,$preference]);
                }
            }
            $pdo->prepare('UPDATE appointment_events SET expanded_at=NOW() WHERE id=?')->execute([$event['id']]);
            $pdo->commit();
        } catch(Throwable $e) {if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    }
}
function notificationDispatch(PDO $pdo, callable $transport, int $limit=50): array
{
    $settings=notificationSettings($pdo);$counts=[];
    $q=$pdo->query("SELECT d.*,e.event_type,e.booking_id,e.booking_date,e.booking_time,e.booking_status,e.staff_id,e.previous_staff_id,e.created_at AS event_created,
        u.fullname,u.email,u.phone_number,u.status AS user_status,u.role
        FROM notification_deliveries d JOIN appointment_events e ON e.id=d.event_id JOIN users u ON u.id=d.user_id
        WHERE d.state IN ('pending','retry') AND d.available_at<=NOW() ORDER BY d.id LIMIT " . max(1,min(200,$limit)));
    foreach($q->fetchAll() as $job) {
        $state='pending';$reason=null;$recovery=$job['event_type']==='password_reset';$resetHash=null;
        if ($job['user_status']!=='active') {$state='skipped';$reason='Account inactive.';}
        if (!$recovery && (!notificationPreference($pdo,(int)$job['user_id'],$job['preference_key']) || !in_array($job['channel'],notificationChannels($settings),true)
            || ($job['event_type']!=='reminder' && empty($settings['booking_confirmation_enabled'])))) {$state='skipped';$reason='Notifications disabled.';}
        $subject='Nails & Body Worx appointment update';$body='';
        if ($recovery) {
            if (strtotime($job['event_created']) < time()-3600) {$state='skipped';$reason='Recovery request expired. Please request another.';}
            // Do not send an old queued request after a newer successful password change.
            $done=$pdo->prepare('SELECT COUNT(*) FROM password_reset_tokens WHERE user_id=? AND used_at>=?');
            $done->execute([$job['user_id'],$job['event_created']]);
            if ($done->fetchColumn()) {$state='skipped';$reason='Recovery request superseded.';}
        } else {
            $bq=$pdo->prepare('SELECT b.*,s.name AS service_name FROM bookings b LEFT JOIN services s ON s.id=b.service_id WHERE b.id=?');
            $bq->execute([$job['booking_id']]);$booking=$bq->fetch();
            if (!$booking || $booking['status']!==$job['booking_status'] || $booking['booking_date']!==$job['booking_date']
                || $booking['booking_time']!==$job['booking_time'] || (int)$booking['staff_id']!==(int)$job['staff_id']) {$state='skipped';$reason='Appointment changed; superseded notification.';}
            if ($job['event_type']==='reminder' && (!$booking || $booking['status']!=='confirmed' || strtotime($booking['booking_date'].' '.$booking['booking_time'])<=time())) {$state='skipped';$reason='Reminder no longer applicable.';}
            if ($booking) {
                $when=date('M j, Y g:i A',strtotime($booking['booking_date'].' '.$booking['booking_time']));
                $label=$job['event_type']==='reminder'?'Reminder':($job['event_type']==='rescheduled'?'Rescheduled appointment':'Appointment update');
                $reference=($booking['booking_code'] ?? '') ?: ('NBW-'.$booking['id']);
                $status=ucwords(str_replace('_',' ',$booking['status']));
                $subject='Nails & Body Worx — '.$label;
                $body="$label: $reference\nService: {$booking['service_name']}\nSchedule: $when\nStatus: $status\n";
                if ((int)$job['previous_staff_id']===(int)$job['user_id'] && (int)$job['staff_id']!==(int)$job['user_id']) $body.="This appointment is no longer assigned to you.\n";
                $body.="Please sign in to SmartBooking to view the appointment.";
                if ($job['channel']==='sms') $body="NBW $label: $reference, $when. Status: $status. Sign in for details.";
            }
        }
        if ($state==='pending') {
            // Persist 'sending' before the external call. A crash is not blindly retried.
            $pdo->prepare("UPDATE notification_deliveries SET state='sending',attempts=attempts+1 WHERE id=?")->execute([$job['id']]);
            try {
                if ($recovery) {
                    $base=messageBaseUrl();$token=bin2hex(random_bytes(32));$resetHash=hash('sha256',$token);
                    bookingWriteBegin($pdo);
                    $pdo->prepare('UPDATE password_reset_tokens SET used_at=NOW() WHERE user_id=? AND used_at IS NULL')->execute([$job['user_id']]);
                    $pdo->prepare('INSERT INTO password_reset_tokens(user_id,token_hash,expires_at) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 1 HOUR))')->execute([$job['user_id'],$resetHash]);
                    $pdo->commit();
                    $subject='Reset your SmartBooking password';
                    $body="Hello {$job['fullname']},\n\nUse this link to reset your password. It expires in one hour and can be used only once:\n".$base.'/auth/reset-password.php?token='.$token."\n\nIf you did not request this, you can ignore this email.";
                }
                $recipient=$job['channel']==='sms'?(string)$job['phone_number']:(string)$job['email'];
                $result=$transport($job['channel'],$recipient,(string)$job['fullname'],$subject,$body,(string)$settings['sms_sender_name'],(string)($settings['reply_to_email']??''));
                $state=$result['state'];$reason=$result['error']??null;
            } catch(Throwable $e) {
                if($pdo->inTransaction())$pdo->rollBack();
                $state='failed';$reason='Message preparation failed; check configuration.';
            }
            if($resetHash && in_array($state,['retry','failed','skipped'],true)) $pdo->prepare('DELETE FROM password_reset_tokens WHERE token_hash=? AND used_at IS NULL')->execute([$resetHash]);
            if($state==='retry' && (int)$job['attempts']>=4) {$state='failed';$reason='Retry limit reached. '.$reason;}
        }
        if(!in_array($state,['sent','retry','failed','skipped','uncertain'],true)) {$state='uncertain';$reason='Unexpected transport result.';}
        $delay=min(3600,60*(2**min(6,(int)$job['attempts'])));
        $pdo->prepare("UPDATE notification_deliveries SET state=?,last_error=?,sent_at=IF(?='sent',NOW(),NULL),available_at=DATE_ADD(NOW(),INTERVAL ? SECOND) WHERE id=?")
            ->execute([$state,$reason,$state,$delay,$job['id']]);
        $counts[$state]=($counts[$state]??0)+1;
    }
    return $counts;
}
