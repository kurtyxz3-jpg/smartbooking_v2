<?php
declare(strict_types=1);

function aiSettings(PDO $pdo): array
{
    $defaults = [
        'ai_enabled' => '1',
        'nail_generator_enabled' => '1',
        'lip_preview_enabled' => '1',
        'eyebrow_preview_enabled' => '1',
        'chatbot_enabled' => '0',
        'receipt_verification_enabled' => '0',
        'style_recommendation_enabled' => '0',
        'ai_service_url' => 'http://127.0.0.1:5000',
        'daily_customer_limit' => '10',
        'images_per_request' => '3',
        'request_timeout_seconds' => '240',
        'image_model' => 'gpt-image-2',
        'beauty_photo_retention_hours' => '24',
    ];

    try {
        $rows = $pdo->query(
            "SELECT setting_key, setting_value FROM ai_settings"
        )->fetchAll();
        foreach ($rows as $row) {
            $defaults[(string) $row['setting_key']] =
                (string) ($row['setting_value'] ?? '');
        }
    } catch (Throwable $exception) {
        // Defaults keep the page usable before the Phase 2 SQL is imported.
    }

    return $defaults;
}

function saveAiSetting(PDO $pdo, string $key, string $value, ?int $userId): void
{
    $statement = $pdo->prepare(
        "INSERT INTO ai_settings
            (setting_key, setting_value, is_secret, updated_by)
         VALUES (?, ?, 0, ?)
         ON DUPLICATE KEY UPDATE
            setting_value = VALUES(setting_value),
            updated_by = VALUES(updated_by)"
    );
    $statement->execute([$key, $value, $userId]);
}

function aiLocalServiceUrl(string $url): string
{
    $url = rtrim(trim($url), '/');
    $parts = parse_url($url);
    $host = strtolower((string) ($parts['host'] ?? ''));
    if (!in_array($host, ['127.0.0.1', 'localhost'], true)) {
        throw new InvalidArgumentException(
            'For local development, the AI service must use localhost or 127.0.0.1.'
        );
    }
    return $url;
}

function callAiService(
    string $baseUrl,
    string $path,
    ?array $payload = null,
    int $timeout = 15
): array {
    if (!function_exists('curl_init')) {
        throw new RuntimeException(
            'PHP cURL is disabled. Enable extension=curl in php.ini and restart Apache.'
        );
    }

    $url = aiLocalServiceUrl($baseUrl) . '/' . ltrim($path, '/');
    $curl = curl_init($url);
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT => max(5, min($timeout, 300)),
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ];

    if ($payload !== null) {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
        $options[CURLOPT_POSTFIELDS] = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    curl_setopt_array($curl, $options);
    $body = curl_exec($curl);
    $error = curl_error($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($body === false || $error !== '') {
        throw new RuntimeException('Python AI service is offline: ' . $error);
    }

    $data = json_decode($body, true);
    if (!is_array($data)) {
        throw new RuntimeException('Python AI service returned invalid data.');
    }
    if ($status < 200 || $status >= 300 || empty($data['success'])) {
        throw new RuntimeException(
            (string) ($data['message'] ?? 'The AI request failed.')
        );
    }

    return $data;
}
