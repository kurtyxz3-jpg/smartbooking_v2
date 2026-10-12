<?php
// Keep secrets in existing config/email-credentials.php or environment variables.
return [
    'timezone' => 'Asia/Manila',
    // Override with NBW_BASE_URL or set this to your final HTTPS website address.
    'base_url' => getenv('NBW_BASE_URL') ?: '',
    // Explicitly enable after configuring Brevo SMS credits and sender approval.
    'sms_provider_enabled' => getenv('NBW_SMS_ENABLED') === '1',
];
