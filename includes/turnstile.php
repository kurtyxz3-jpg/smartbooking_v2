<?php
declare(strict_types=1);

function turnstileConfig(): array
{
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/../config/turnstile.php';
    }
    return is_array($config) ? $config : [];
}

function turnstileSiteKey(): string
{
    return (string) (turnstileConfig()['site_key'] ?? '');
}

function turnstileValidate(string $token, string $expectedAction): bool
{
    $config = turnstileConfig();
    if (empty($config['enabled'])) {
        return true;
    }
    $secret = trim((string) ($config['secret_key'] ?? ''));
    $token = trim($token);
    if ($secret === '' || $token === '' || !function_exists('curl_init')) {
        return false;
    }

    $curl = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    if ($curl === false) {
        return false;
    }
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_POSTFIELDS => http_build_query([
            'secret' => $secret,
            'response' => $token,
            'remoteip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
        ]),
    ]);
    $body = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($status < 200 || $status >= 300 || !is_string($body)) {
        return false;
    }
    $result = json_decode($body, true);
    if (!is_array($result) || empty($result['success'])) {
        return false;
    }
    return empty($result['action']) || hash_equals($expectedAction, (string) $result['action']);
}
