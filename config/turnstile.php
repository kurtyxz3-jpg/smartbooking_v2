<?php
declare(strict_types=1);

$credentialsFile = __DIR__ . '/turnstile-credentials.php';
$credentials = is_file($credentialsFile) ? require $credentialsFile : [];

return [
    'enabled' => true,
    'site_key' => (string) ($credentials['site_key'] ?? '1x00000000000000000000AA'),
    'secret_key' => (string) ($credentials['secret_key'] ?? '1x0000000000000000000000000000000AA'),
];
