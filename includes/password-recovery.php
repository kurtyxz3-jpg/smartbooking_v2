<?php
declare(strict_types=1);
require_once __DIR__ . '/message-transport.php';
function recoveryClock(PDO $pdo): void
{
    date_default_timezone_set(messageConfig()['timezone']);
    $pdo->exec('SET time_zone=' . $pdo->quote((new DateTimeImmutable())->format('P')));
}
function recoveryCsrf(): string
{
    $_SESSION['recovery_csrf'] ??= bin2hex(random_bytes(32));
    return $_SESSION['recovery_csrf'];
}
function recoveryVerifyCsrf(): bool
{
    return hash_equals(recoveryCsrf(), (string)($_POST['csrf_token'] ?? ''));
}
function recoveryRequest(PDO $pdo, string $email): void
{
    recoveryClock($pdo);
    $email = strtolower(trim($email));
    $ip = hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $identity = hash('sha256', $email);
    $pdo->beginTransaction();
    try {
        // Serializes rate-limit check and record, including requests for unknown accounts.
        $pdo->query('SELECT id FROM booking_write_mutex WHERE id=1 FOR UPDATE')->fetchColumn();
        $q=$pdo->prepare('SELECT SUM(ip_hash=?) AS ip_count,SUM(identity_hash=?) AS identity_count FROM recovery_attempts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) AND (ip_hash=? OR identity_hash=?)');
        $q->execute([$ip,$identity,$ip,$identity]);$counts=$q->fetch();
        if ((int)$counts['ip_count'] >= 10 || (int)$counts['identity_count'] >= 3) { $pdo->commit(); return; }
        $pdo->prepare('INSERT INTO recovery_attempts(ip_hash,identity_hash) VALUES(?,?)')->execute([$ip,$identity]);
        if (filter_var($email,FILTER_VALIDATE_EMAIL)) {
            $q=$pdo->prepare("SELECT id FROM users WHERE email=? AND status='active' LIMIT 1");$q->execute([$email]);$id=(int)$q->fetchColumn();
            if ($id) $pdo->prepare("INSERT INTO appointment_events(event_type,user_id) VALUES('password_reset',?)")->execute([$id]);
        }
        $pdo->commit();
    } catch (Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); throw $e; }
}
function recoveryConsume(PDO $pdo, string $token, string $password): bool
{
    recoveryClock($pdo);
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) return false;
    $pdo->beginTransaction();
    try {
        // Global recovery mutex also protects against token issuance by the worker.
        $pdo->query('SELECT id FROM booking_write_mutex WHERE id=1 FOR UPDATE')->fetchColumn();
        $q=$pdo->prepare("SELECT t.id,t.user_id FROM password_reset_tokens t JOIN users u ON u.id=t.user_id WHERE t.token_hash=? AND t.used_at IS NULL AND t.expires_at>NOW() AND u.status='active' FOR UPDATE");
        $q->execute([hash('sha256',$token)]);$record=$q->fetch();
        if (!$record) {$pdo->rollBack();return false;}
        $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($password,PASSWORD_DEFAULT),$record['user_id']]);
        $pdo->prepare('UPDATE password_reset_tokens SET used_at=NOW() WHERE user_id=? AND used_at IS NULL')->execute([$record['user_id']]);
        $pdo->prepare('INSERT INTO account_security_versions(user_id,version) VALUES(?,1) ON DUPLICATE KEY UPDATE version=version+1')->execute([$record['user_id']]);
        $pdo->commit();return true;
    } catch (Throwable $e) {if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
