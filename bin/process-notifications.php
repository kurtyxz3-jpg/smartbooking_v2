<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {http_response_code(404);exit;}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/notification-worker.php';
date_default_timezone_set(messageConfig()['timezone']);
// NOW() and appointment timestamps use the same salon timezone as PHP.
$offset=(new DateTimeImmutable())->format('P');$pdo->exec('SET time_zone='.$pdo->quote($offset));
$send=in_array('--send',$argv,true);
if (!$send) {
    echo "Read-only check. Use --send from your scheduled task to process messages.\n";
    echo json_encode($pdo->query('SELECT state,COUNT(*) AS count FROM notification_deliveries GROUP BY state')->fetchAll(),JSON_PRETTY_PRINT)."\n";
    exit;
}
$lock='nbw-notify-'.substr(hash('sha256',(string)$pdo->query('SELECT DATABASE()')->fetchColumn()),0,32);
$q=$pdo->prepare('SELECT GET_LOCK(?,0)');$q->execute([$lock]);
if((int)$q->fetchColumn()!==1){echo "Another worker is running.\n";exit;}
try {
    // Prior worker crashes leave sending entries for manual provider-log review.
    $pdo->exec("UPDATE notification_deliveries SET state='uncertain',last_error='Previous worker interrupted; check provider log.' WHERE state='sending'");
    notificationPrepare($pdo);
    echo json_encode(notificationDispatch($pdo,'messageSend'),JSON_PRETTY_PRINT)."\n";
} catch(Throwable $e) {fwrite(STDERR,"Notification processing failed. Check migration and configuration.\n");exit(1);}
finally {$q=$pdo->prepare('SELECT RELEASE_LOCK(?)');$q->execute([$lock]);}
