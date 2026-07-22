<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Social Login Configuration
|--------------------------------------------------------------------------
|
| Replace the client IDs and secrets below after creating applications in:
|
| Google Cloud Console:
| https://console.cloud.google.com/
|
| Meta for Developers:
| https://developers.facebook.com/
|
| Never commit real secrets to a public GitHub repository.
|
*/

const GOOGLE_OAUTH_CLIENT_ID = '';
const GOOGLE_OAUTH_CLIENT_SECRET = '';
const GOOGLE_OAUTH_REDIRECT_URI =
    'http://localhost/smartbooking_v2/auth/google-callback.php';

const FACEBOOK_APP_ID = '';
const FACEBOOK_APP_SECRET = '';
const FACEBOOK_REDIRECT_URI =
    'http://localhost/smartbooking_v2/auth/facebook-callback.php';

function oauthProviderEnabled(string $provider): bool
{
    return match ($provider) {
        'google' =>
            GOOGLE_OAUTH_CLIENT_ID !== ''
            && GOOGLE_OAUTH_CLIENT_SECRET !== '',
        'facebook' =>
            FACEBOOK_APP_ID !== ''
            && FACEBOOK_APP_SECRET !== '',
        default => false,
    };
}

function oauthHttpGet(string $url): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException(
            'The PHP cURL extension is required for social login.'
        );
    }

    $curl = curl_init($url);

    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);

    $response = curl_exec($curl);
    $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);

    curl_close($curl);

    if ($response === false || $statusCode >= 400) {
        throw new RuntimeException(
            'Social login request failed. ' . $error
        );
    }

    $data = json_decode($response, true);

    if (!is_array($data)) {
        throw new RuntimeException('Invalid social login response.');
    }

    return $data;
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
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException(
            'The social account did not provide a valid email address.'
        );
    }

    $statement = $pdo->prepare(
        "SELECT
            u.id,
            u.fullname,
            u.username,
            u.email,
            u.role,
            u.status
         FROM social_accounts sa
         JOIN users u ON u.id = sa.user_id
         WHERE sa.provider = ?
           AND sa.provider_user_id = ?
         LIMIT 1"
    );
    $statement->execute([$provider, $providerUserId]);
    $user = $statement->fetch();

    if (!$user) {
        $statement = $pdo->prepare(
            "SELECT id, fullname, username, email, role, status
             FROM users
             WHERE email = ?
             LIMIT 1"
        );
        $statement->execute([$email]);
        $user = $statement->fetch();

        if ($user && $user['role'] !== 'customer') {
            throw new RuntimeException(
                'This email is already connected to a non-customer account.'
            );
        }
    }

    $pdo->beginTransaction();

    try {
        if (!$user) {
            $usernameBase = preg_replace(
                '/[^a-z0-9]/',
                '',
                strtolower($firstName . $lastName)
            );

            if ($usernameBase === '') {
                $usernameBase = preg_replace(
                    '/[^a-z0-9]/',
                    '',
                    strtolower(strstr($email, '@', true) ?: 'customer')
                );
            }

            if ($usernameBase === '') {
                $usernameBase = 'customer';
            }

            $username = $usernameBase;
            $suffix = 1;

            while (true) {
                $check = $pdo->prepare(
                    "SELECT id
                     FROM users
                     WHERE username = ?
                     LIMIT 1"
                );
                $check->execute([$username]);

                if (!$check->fetch()) {
                    break;
                }

                $username = $usernameBase . $suffix;
                $suffix++;
            }

            $temporaryPassword = bin2hex(random_bytes(24));

            $statement = $pdo->prepare(
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
            $statement->execute([
                'fullname' => $fullname,
                'username' => $username,
                'email' => $email,
                'password' => password_hash(
                    $temporaryPassword,
                    PASSWORD_DEFAULT
                ),
            ]);

            $userId = (int) $pdo->lastInsertId();

            $nameParts = preg_split('/\s+/', trim($fullname));
            $resolvedFirstName =
                $firstName ?: ($nameParts[0] ?? 'Customer');
            $resolvedLastName =
                $lastName ?: (count($nameParts) > 1
                    ? end($nameParts)
                    : '');

            $statement = $pdo->prepare(
                "INSERT INTO customer_profiles (
                    user_id,
                    first_name,
                    last_name,
                    registration_method,
                    email_verified_at
                ) VALUES (?, ?, ?, ?, NOW())"
            );
            $statement->execute([
                $userId,
                $resolvedFirstName,
                $resolvedLastName,
                $provider,
            ]);

            $user = [
                'id' => $userId,
                'fullname' => $fullname,
                'username' => $username,
                'email' => $email,
                'role' => 'customer',
                'status' => 'active',
            ];
        } else {
            $userId = (int) $user['id'];
        }

        $statement = $pdo->prepare(
            "INSERT INTO social_accounts (
                user_id,
                provider,
                provider_user_id,
                provider_email
            ) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                user_id = VALUES(user_id),
                provider_email = VALUES(provider_email)"
        );
        $statement->execute([
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
        throw new RuntimeException('This account is inactive.');
    }

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = 'customer';
}
