<?php
declare(strict_types=1);

function registrationSecurityConfig(): array
{
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/../config/registration-security.php';
    }
    return $config;
}

function registrationSecurityEmailConfig(): array
{
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/../config/email.php';
    }
    return $config;
}

function registrationSecurityEmailCredentials(): array
{
    static $credentials;
    if ($credentials === null) {
        $file = __DIR__ . '/../config/email-credentials.php';
        $credentials = is_file($file) ? require $file : [];
    }
    return is_array($credentials) ? $credentials : [];
}

function registrationSecurityIpHash(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $pepper = (string) (registrationSecurityConfig()['pepper'] ?? '');
    return hash('sha256', $ip . '|' . $pepper);
}

function registrationSecurityIdentityHash(string $identity): string
{
    $pepper = (string) (registrationSecurityConfig()['pepper'] ?? '');
    return hash('sha256', strtolower(trim($identity)) . '|' . $pepper);
}

function registrationSecurityCount(PDO $pdo, string $event, string $identity = ''): int
{
    $sql = "SELECT COUNT(*) FROM registration_security_events
            WHERE event_type = ? AND ip_hash = ?
              AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
    $params = [$event, registrationSecurityIpHash()];
    if ($identity !== '') {
        $sql .= ' AND identity_hash = ?';
        $params[] = registrationSecurityIdentityHash($identity);
    }
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    return (int) $statement->fetchColumn();
}

function registrationSecurityRecord(
    PDO $pdo,
    string $event,
    string $identity = '',
    ?int $userId = null
): void {
    $statement = $pdo->prepare(
        'INSERT INTO registration_security_events
            (event_type, identity_hash, ip_hash, user_id)
         VALUES (?, ?, ?, ?)'
    );
    $statement->execute([
        $event,
        $identity !== '' ? registrationSecurityIdentityHash($identity) : null,
        registrationSecurityIpHash(),
        $userId,
    ]);
}

function registrationSecurityIssueEmailToken(PDO $pdo, int $userId): string
{
    $token = bin2hex(random_bytes(32));
    $hours = max(
        1,
        (int) (registrationSecurityConfig()['verification_token_lifetime_hours'] ?? 24)
    );
    $pdo->prepare(
        'UPDATE email_verification_tokens SET used_at = NOW()
         WHERE user_id = ? AND used_at IS NULL'
    )->execute([$userId]);
    $statement = $pdo->prepare(
        'INSERT INTO email_verification_tokens
            (user_id, token_hash, expires_at)
         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? HOUR))'
    );
    $statement->execute([$userId, hash('sha256', $token), $hours]);
    return $token;
}

function registrationSecurityIssueEmailChallenge(PDO $pdo, int $userId): array
{
    $token = bin2hex(random_bytes(32));
    $code = (string) random_int(100000, 999999);
    $minutes = max(
        5,
        (int) (registrationSecurityConfig()['verification_code_lifetime_minutes'] ?? 10)
    );

    $pdo->prepare(
        'UPDATE email_verification_tokens SET used_at = NOW()
         WHERE user_id = ? AND used_at IS NULL'
    )->execute([$userId]);

    $statement = $pdo->prepare(
        'INSERT INTO email_verification_tokens
            (user_id, token_hash, verification_code_hash, expires_at)
         VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))'
    );
    $statement->execute([
        $userId,
        hash('sha256', $token),
        hash('sha256', $code),
        $minutes,
    ]);

    return ['token' => $token, 'code' => $code];
}

function registrationSecurityVerificationUrl(string $token): string
{
    $config = registrationSecurityEmailConfig();
    return rtrim((string) $config['base_url'], '/')
        . '/auth/verify-email.php?token=' . urlencode($token);
}

function registrationSecuritySendVerification(
    string $email,
    string $fullname,
    string $url,
    string $code
): bool {
    $config = registrationSecurityEmailConfig();
    $mode = (string) ($config['mode'] ?? 'development');

    if ($mode === 'development') {
        $_SESSION['development_verification_url'] = $url;
        $_SESSION['development_verification_code'] = $code;
        return true;
    }

    if ($mode === 'brevo') {
        $credentials = registrationSecurityEmailCredentials();
        $apiKey = trim((string) ($credentials['brevo_api_key'] ?? ''));
        if ($apiKey === '' || str_starts_with($apiKey, 'PASTE_')) {
            return false;
        }

        $safeName = htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8');
        $safeCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
        $payload = [
            'sender' => [
                'name' => (string) ($config['from_name'] ?? 'Nails & Body Worx'),
                'email' => (string) ($config['from_email'] ?? ''),
            ],
            'to' => [['email' => $email, 'name' => $fullname]],
            'subject' => 'Your SmartBooking verification code',
            'htmlContent' => '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;color:#2a1a12">'
                . '<h2 style="color:#a97922">Nails &amp; Body Worx SmartBooking</h2>'
                . '<p>Hello ' . $safeName . ',</p>'
                . '<p>Use this six-digit code to verify your account:</p>'
                . '<div style="font-size:34px;font-weight:700;letter-spacing:10px;padding:20px;text-align:center;background:#f8f5f0;border:1px solid #d8b56a;border-radius:12px">'
                . $safeCode . '</div>'
                . '<p>This code expires in 10 minutes and can be used only once.</p>'
                . '<p>If you did not create this account, you can ignore this message.</p>'
                . '</div>',
        ];

        if (!function_exists('curl_init')) {
            return false;
        }
        $curl = curl_init('https://api.brevo.com/v3/smtp/email');
        if ($curl === false) {
            return false;
        }
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'api-key: ' . $apiKey,
                'content-type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
        ]);
        curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $status >= 200 && $status < 300;
    }

    $subject = 'Verify your SmartBooking email';
    $body = "Hello {$fullname},\n\nYour SmartBooking verification code is: {$code}\n\n"
        . "The code expires and can be used only once.\n\nVerification link: {$url}";
    $headers = 'From: ' . ($config['from_name'] ?? 'SmartBooking')
        . ' <' . ($config['from_email'] ?? 'no-reply@example.com') . ">\r\n";
    return mail($email, $subject, $body, $headers);
}

function registrationSecurityIsLocalRequest(): bool
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    return str_starts_with($host, 'localhost')
        || str_starts_with($host, '127.0.0.1');
}

function registrationSecurityManualEmailVerified(PDO $pdo, int $userId): bool
{
    $statement = $pdo->prepare(
        "SELECT cp.registration_method, cp.email_verified_at
         FROM customer_profiles cp
         WHERE cp.user_id = ? LIMIT 1"
    );
    $statement->execute([$userId]);
    $profile = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$profile) {
        return false;
    }
    if (in_array($profile['registration_method'], ['google', 'facebook'], true)) {
        return true;
    }
    return !empty($profile['email_verified_at']);
}

function registrationSecurityCustomerProfile(PDO $pdo, int $userId): array
{
    $statement = $pdo->prepare(
        "SELECT u.username, u.phone_number, cp.first_name, cp.last_name,
                cp.registration_method
         FROM users u
         LEFT JOIN customer_profiles cp ON cp.user_id = u.id
         WHERE u.id = ? AND u.role = 'customer' LIMIT 1"
    );
    $statement->execute([$userId]);
    $profile = $statement->fetch(PDO::FETCH_ASSOC);
    return is_array($profile) ? $profile : [];
}

function registrationSecurityCustomerProfileComplete(PDO $pdo, int $userId): bool
{
    $profile = registrationSecurityCustomerProfile($pdo, $userId);
    foreach (['username', 'phone_number', 'first_name', 'last_name'] as $field) {
        if (trim((string) ($profile[$field] ?? '')) === '') {
            return false;
        }
    }
    return true;
}

function registrationSecurityRequirePendingVerification(PDO $pdo, int $userId): void
{
    $statement = $pdo->prepare(
        'SELECT fullname, email FROM users WHERE id = ? LIMIT 1'
    );
    $statement->execute([$userId]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['pending_verification_user_id'] = $userId;
        $_SESSION['pending_verification_email'] = (string) $user['email'];
        $_SESSION['pending_verification_name'] = (string) $user['fullname'];
    }

    foreach (['user_id', 'fullname', 'username', 'email', 'role', 'branch_id'] as $key) {
        unset($_SESSION[$key]);
    }
}
