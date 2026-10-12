<?php
declare(strict_types=1);
function sessionSecurityVersion(PDO $pdo, int $id): int
{
    $q=$pdo->prepare('SELECT version FROM account_security_versions WHERE user_id=?');$q->execute([$id]);return (int)$q->fetchColumn();
}
function sessionSecurityCapture(PDO $pdo, ?string $verifiedHash = null): void
{
    $q=$pdo->prepare('SELECT u.password,u.status,COALESCE(v.version,0) AS version FROM users u LEFT JOIN account_security_versions v ON v.user_id=u.id WHERE u.id=?');
    $q->execute([(int)$_SESSION['user_id']]);$account=$q->fetch();
    if(!$account || $account['status']!=='active' || ($verifiedHash!==null && !hash_equals($account['password'],$verifiedHash))) {
        $_SESSION=[];$_SESSION['login_notice']='Your account changed. Please sign in again.';
        header('Location: /smartbooking_v2/auth/login.php');exit;
    }
    $_SESSION['security_version']=(int)$account['version'];
}
function sessionSecurityCheck(PDO $pdo): void
{
    if(empty($_SESSION['user_id'])) return;
    $q=$pdo->prepare('SELECT u.status,COALESCE(v.version,0) AS version FROM users u LEFT JOIN account_security_versions v ON v.user_id=u.id WHERE u.id=?');
    $q->execute([(int)$_SESSION['user_id']]);$account=$q->fetch();
    if(!$account || $account['status']!=='active' || (int)($_SESSION['security_version']??0)!==(int)$account['version']) {
        $_SESSION=[];
        session_regenerate_id(true);
        $_SESSION['login_notice']='Your account changed. Please sign in again.';
    }
}
