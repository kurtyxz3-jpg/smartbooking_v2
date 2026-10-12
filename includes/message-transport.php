<?php
declare(strict_types=1);
function messageConfig(): array
{
    $email = require __DIR__ . '/../config/email.php';
    $runtime = require __DIR__ . '/../config/reliability.php';
    $runtime['base_url'] = rtrim($runtime['base_url'] ?: ($email['base_url'] ?? ''), '/');
    return array_merge($email, $runtime);
}
function messageBaseUrl(): string
{
    $url = (string)messageConfig()['base_url'];
    $parts = parse_url($url);
    if (!$parts || !isset($parts['host']) || !in_array($parts['scheme'] ?? '', ['http','https'], true)
        || isset($parts['user']) || isset($parts['query']) || isset($parts['fragment'])
        || (($parts['scheme'] ?? '') !== 'https' && !in_array($parts['host'], ['localhost','127.0.0.1'], true))) {
        throw new RuntimeException('Configure the public HTTPS base URL.');
    }
    return $url;
}
function messagePhone(string $phone): string
{
    $phone = preg_replace('/[\s().-]/', '', $phone) ?? '';
    if (preg_match('/^09\d{9}$/', $phone)) $phone = '+63' . substr($phone, 1);
    if (preg_match('/^63\d{10}$/', $phone)) $phone = '+' . $phone;
    return preg_match('/^\+[1-9]\d{7,14}$/', $phone) ? $phone : '';
}
function messageSend(string $channel, string $recipient, string $name, string $subject, string $body, string $sender = 'NBW', string $replyTo = ''): array
{
    $config = messageConfig();
    $file = __DIR__ . '/../config/email-credentials.php';
    $credentials = is_file($file) ? require $file : [];
    $key = trim((string)(getenv('BREVO_API_KEY') ?: ($credentials['brevo_api_key'] ?? '')));
    if ($key === '' || str_starts_with($key, 'PASTE_') || !function_exists('curl_init')) {
        return ['state'=>'retry', 'error'=>'Email provider credentials or PHP cURL missing.'];
    }
    if ($channel === 'sms') {
        if (!$config['sms_provider_enabled']) return ['state'=>'skipped','error'=>'SMS provider is not enabled.'];
        $recipient = messagePhone($recipient);
        if ($recipient === '') return ['state'=>'failed','error'=>'Invalid phone number.'];
        $payload = ['sender'=>$sender, 'recipient'=>$recipient, 'content'=>$body, 'type'=>'transactional'];
        $endpoint = 'transactionalSMS/send';
    } else {
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL) || !filter_var($config['from_email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            return ['state'=>'failed','error'=>'Invalid sender or recipient email.'];
        }
        $payload = ['sender'=>['email'=>$config['from_email'],'name'=>$config['from_name'] ?? 'Nails & Body Worx'],
            'to'=>[['email'=>$recipient,'name'=>$name]], 'subject'=>$subject, 'textContent'=>$body];
        if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $payload['replyTo']=['email'=>$replyTo];
        $endpoint = 'smtp/email';
    }
    $curl = curl_init('https://api.brevo.com/v3/' . $endpoint);
    curl_setopt_array($curl, [CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>20,
        CURLOPT_HTTPHEADER=>['api-key: '.$key,'content-type: application/json','accept: application/json'],
        CURLOPT_POSTFIELDS=>json_encode($payload, JSON_UNESCAPED_SLASHES)]);
    $response = curl_exec($curl);
    $status = (int)curl_getinfo($curl,CURLINFO_HTTP_CODE);
    $errno = curl_errno($curl);
    curl_close($curl);
    if ($status >= 200 && $status < 300) return ['state'=>'sent','error'=>null];
    // A timeout/5xx may occur AFTER acceptance. Hold for provider review instead
    // of retrying automatically and sending duplicate messages.
    if ($response === false || $status >= 500) return ['state'=>'uncertain','error'=>'Provider response uncertain; check provider log before retrying.'];
    if (in_array($status, [401,403,429],true)) return ['state'=>'retry','error'=>'Provider HTTP '.$status.'; check credentials, quota or limits.'];
    return ['state'=>'failed','error'=>'Provider rejected message (HTTP '.$status.').'];
}
