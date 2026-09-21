<?php
declare(strict_types=1);

$credentialFile = __DIR__ . '/oauth-credentials.php';

$oauthCredentials = is_file($credentialFile)
    ? require $credentialFile
    : [];

function oauthConfig(string $provider, ?string $key = null): mixed
{
    global $oauthCredentials;

    $providerConfig = $oauthCredentials[$provider] ?? [];

    return $key === null
        ? $providerConfig
        : ($providerConfig[$key] ?? null);
}

function oauthPlaceholderValue(?string $value): bool
{
    if ($value === null || trim($value) === '') {
        return true;
    }

    return str_contains($value, 'PASTE_')
        || str_contains($value, '_HERE');
}

function oauthProviderEnabled(string $provider): bool
{
    return match ($provider) {
        'google' =>
            !oauthPlaceholderValue((string) oauthConfig('google', 'client_id'))
            && !oauthPlaceholderValue(
                (string) oauthConfig('google', 'client_secret')
            ),
        'facebook' =>
            !oauthPlaceholderValue((string) oauthConfig('facebook', 'app_id'))
            && !oauthPlaceholderValue(
                (string) oauthConfig('facebook', 'app_secret')
            ),
        default => false,
    };
}

function oauthRequest(
    string $url,
    string $method = 'GET',
    array $parameters = [],
    array $headers = []
): array {
    if (!function_exists('curl_init')) {
        throw new RuntimeException(
            'PHP cURL is disabled. Enable extension=curl in php.ini.'
        );
    }

    $method = strtoupper($method);

    if ($method === 'GET' && $parameters) {
        $url .= (str_contains($url, '?') ? '&' : '?')
            . http_build_query($parameters);
    }

    $curl = curl_init($url);

    $requestHeaders = array_merge(
        ['Accept: application/json'],
        $headers
    );

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $requestHeaders,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ];

    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($parameters);
        $requestHeaders[] =
            'Content-Type: application/x-www-form-urlencoded';
        $options[CURLOPT_HTTPHEADER] = $requestHeaders;
    }

    curl_setopt_array($curl, $options);

    $response = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);

    curl_close($curl);

    if ($response === false) {
        throw new RuntimeException(
            'OAuth network request failed: ' . $curlError
        );
    }

    $data = json_decode($response, true);

    if (!is_array($data)) {
        throw new RuntimeException(
            'The OAuth provider returned an invalid response.'
        );
    }

    if ($status < 200 || $status >= 300) {
        $providerMessage =
            $data['error_description']
            ?? $data['error']['message']
            ?? $data['error']
            ?? 'Unknown OAuth error';

        if (is_array($providerMessage)) {
            $providerMessage = json_encode($providerMessage);
        }

        throw new RuntimeException(
            'OAuth request failed (' . $status . '): '
            . $providerMessage
        );
    }

    return $data;
}

function oauthCreateUniqueUsername(
    PDO $pdo,
    string $firstName,
    string $lastName,
    string $email
): string {
    $base = preg_replace(
        '/[^a-z0-9]/',
        '',
        strtolower($firstName . $lastName)
    );

    if ($base === '') {
        $base = preg_replace(
            '/[^a-z0-9]/',
            '',
            strtolower(strstr($email, '@', true) ?: 'customer')
        );
    }

    $base = substr($base ?: 'customer', 0, 40);
    $username = $base;
    $suffix = 1;

    $statement = $pdo->prepare(
        "SELECT COUNT(*) FROM users WHERE username = ?"
    );

    while (true) {
        $statement->execute([$username]);

        if ((int) $statement->fetchColumn() === 0) {
            return $username;
        }

        $username = substr($base, 0, 35) . $suffix;
        $suffix++;
    }
}

function oauthFindExistingCustomer(
    PDO $pdo,
    string $provider,
    string $providerUserId,
    string $email
): ?array {
    $statement = $pdo->prepare(
        "SELECT DISTINCT u.id, u.fullname, u.username, u.email, u.role, u.status
         FROM users u
         LEFT JOIN social_accounts sa
           ON sa.user_id = u.id AND sa.provider = :provider
         WHERE u.role = 'customer'
           AND (sa.provider_user_id = :provider_id OR u.email = :email)
         LIMIT 1"
    );
    $statement->execute([
        'provider' => strtolower(trim($provider)),
        'provider_id' => trim($providerUserId),
        'email' => strtolower(trim($email)),
    ]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    return $user ?: null;
}

function oauthCreateOrLoginCustomer(
    PDO $pdo,
    string $provider,
    string $providerUserId,
    string $email,
    string $fullname,
    ?string $firstName = null,
    ?string $lastName = null
): void {
    $provider = strtolower(trim($provider));
    $providerUserId = trim($providerUserId);
    $email = strtolower(trim($email));
    $fullname = trim($fullname);

    if (!in_array($provider, ['google', 'facebook'], true)) {
        throw new RuntimeException('Unsupported social-login provider.');
    }

    if ($providerUserId === '') {
        throw new RuntimeException(
            'The provider did not return a user identifier.'
        );
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException(
            'The social account did not provide a usable email address.'
        );
    }

    if ($fullname === '') {
        $fullname = $email;
    }

    $socialStatement = $pdo->prepare(
        "SELECT
            u.id,
            u.fullname,
            u.username,
            u.email,
            u.role,
            u.status
         FROM social_accounts sa
         INNER JOIN users u ON u.id = sa.user_id
         WHERE sa.provider = ?
           AND sa.provider_user_id = ?
         LIMIT 1"
    );
    $socialStatement->execute([$provider, $providerUserId]);
    $user = $socialStatement->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $emailStatement = $pdo->prepare(
            "SELECT id, fullname, username, email, role, status
             FROM users
             WHERE email = ?
             LIMIT 1"
        );
        $emailStatement->execute([$email]);
        $user = $emailStatement->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['role'] !== 'customer') {
            throw new RuntimeException(
                'That email belongs to a management account and cannot '
                . 'be linked as a customer login.'
            );
        }
    }

    $pdo->beginTransaction();

    try {
        if (!$user) {
            $resolvedFirstName = trim((string) $firstName);
            $resolvedLastName = trim((string) $lastName);

            if ($resolvedFirstName === '') {
                $parts = preg_split('/\s+/', $fullname);
                $resolvedFirstName = $parts[0] ?? 'Customer';
            }

            if ($resolvedLastName === '') {
                $parts = preg_split('/\s+/', $fullname);
                $resolvedLastName =
                    count($parts) > 1
                        ? (string) end($parts)
                        : '';
            }

            $username = oauthCreateUniqueUsername(
                $pdo,
                $resolvedFirstName,
                $resolvedLastName,
                $email
            );

            $randomPassword = bin2hex(random_bytes(32));

            $insertUser = $pdo->prepare(
                "INSERT INTO users (
                    fullname,
                    username,
                    email,
                    phone_number,
                    password,
                    role,
                    status
                ) VALUES (
                    :fullname,
                    :username,
                    :email,
                    NULL,
                    :password,
                    'customer',
                    'active'
                )"
            );
            $insertUser->execute([
                'fullname' => $fullname,
                'username' => $username,
                'email' => $email,
                'password' => password_hash(
                    $randomPassword,
                    PASSWORD_DEFAULT
                ),
            ]);

            $userId = (int) $pdo->lastInsertId();

            $insertProfile = $pdo->prepare(
                "INSERT INTO customer_profiles (
                    user_id,
                    first_name,
                    middle_name,
                    last_name,
                    preferred_language,
                    registration_method,
                    email_verified_at
                ) VALUES (
                    :user_id,
                    :first_name,
                    NULL,
                    :last_name,
                    'english',
                    :provider,
                    NOW()
                )"
            );
            $insertProfile->execute([
                'user_id' => $userId,
                'first_name' => $resolvedFirstName,
                'last_name' => $resolvedLastName,
                'provider' => $provider,
            ]);

            $user = [
                'id' => $userId,
                'fullname' => $fullname,
                'username' => $username,
                'email' => $email,
                'role' => 'customer',
                'status' => 'active',
            ];
        }

        $userId = (int) $user['id'];

        $linkSocial = $pdo->prepare(
            "INSERT INTO social_accounts (
                user_id,
                provider,
                provider_user_id,
                provider_email
            ) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                user_id = VALUES(user_id),
                provider_email = VALUES(provider_email),
                updated_at = CURRENT_TIMESTAMP"
        );
        $linkSocial->execute([
            $userId,
            $provider,
            $providerUserId,
            $email,
        ]);

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }

    if (($user['status'] ?? '') !== 'active') {
        throw new RuntimeException(
            'This SmartBooking account is inactive.'
        );
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = 'customer';
    $_SESSION['social_provider'] = $provider;
}

function oauthCustomerDestination(): string
{
    return !empty($_SESSION['booking_draft'])
        ? '../customer/book.php?restore=1'
        : '../customer/dashboard.php';
}
