<?php
declare(strict_types=1);

return [
    'registration_limit_per_hour' => 5,
    'login_failure_limit_per_hour' => 10,
    'new_account_window_hours' => 24,
    'new_account_max_active_bookings' => 1,
    'verification_resend_limit_per_hour' => 3,
    'verification_resend_cooldown_seconds' => 60,
    'verification_token_lifetime_hours' => 24,
    'verification_code_lifetime_minutes' => 10,
    'verification_code_max_attempts' => 5,
    'pepper' => 'change-this-to-a-long-random-secret-before-public-deployment',
];
