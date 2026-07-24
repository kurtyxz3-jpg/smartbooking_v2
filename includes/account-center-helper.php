<?php
declare(strict_types=1);

function accountCenterCsrf(): string
{
    if (empty($_SESSION['account_center_csrf'])) {
        $_SESSION['account_center_csrf'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['account_center_csrf'];
}

function accountCenterVerifyCsrf(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['account_center_csrf'])
        && hash_equals((string) $_SESSION['account_center_csrf'], $token);
}

function accountCenterFlash(
    string $type,
    string $message,
    string $location = 'profile.php'
): never {
    $_SESSION['account_center_flash'] = [
        'type' => $type,
        'message' => $message,
    ];

    header('Location: ' . $location);
    exit;
}

function accountCenterGetFlash(): ?array
{
    $flash = $_SESSION['account_center_flash'] ?? null;
    unset($_SESSION['account_center_flash']);

    return is_array($flash) ? $flash : null;
}

function accountCenterTableExists(PDO $pdo, string $table): bool
{
    $statement = $pdo->prepare(
        "SELECT COUNT(*)
         FROM information_schema.tables
         WHERE table_schema = DATABASE()
           AND table_name = ?"
    );
    $statement->execute([$table]);

    return (bool) $statement->fetchColumn();
}

function accountCenterRoleName(string $role): string
{
    return match ($role) {
        'super_admin' => 'Super Administrator',
        'admin' => 'Administrator',
        'staff' => 'Staff Member',
        'customer' => 'Customer',
        default => ucwords(str_replace('_', ' ', $role)),
    };
}

function accountCenterInitials(string $fullname): string
{
    $parts = preg_split('/\s+/', trim($fullname)) ?: [];
    $initials = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    return $initials !== '' ? $initials : 'NB';
}

function accountCenterUploadProfileImage(
    array $file,
    ?string $existing = null
): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $existing;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The profile photo could not be uploaded.');
    }

    if (($file['size'] ?? 0) > 4 * 1024 * 1024) {
        throw new RuntimeException('The profile photo must be 4 MB or smaller.');
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);

    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Use a JPG, PNG, or WebP profile photo.');
    }

    $directory = dirname(__DIR__) . '/uploads/profile';

    if (!is_dir($directory) && !mkdir($directory, 0775, true)) {
        throw new RuntimeException('The profile upload folder is unavailable.');
    }

    $filename =
        'profile-'
        . date('Ymd-His')
        . '-'
        . bin2hex(random_bytes(6))
        . '.'
        . $extensions[$mime];

    if (!move_uploaded_file($tmp, $directory . '/' . $filename)) {
        throw new RuntimeException('The profile photo could not be saved.');
    }

    if ($existing) {
        $oldPath = $directory . '/' . basename($existing);

        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    return $filename;
}

function accountCenterPermissions(string $role): array
{
    return match ($role) {
        'super_admin' => [
            ['Manage administrators', true],
            ['System and AI settings', true],
            ['Audit logs', true],
            ['Backup and restore', true],
            ['All reports and operations', true],
            ['Customer booking interface', false],
        ],
        'admin' => [
            ['Bookings and staff assignment', true],
            ['Services and inventory', true],
            ['Staff and promotions', true],
            ['Payments, feedback, and reports', true],
            ['Manage administrators', false],
            ['System backup and AI settings', false],
        ],
        'staff' => [
            ['Assigned appointments', true],
            ['Personal schedule', true],
            ['Customer service status updates', true],
            ['Personal performance', true],
            ['Change prices and services', false],
            ['System and administrator settings', false],
        ],
        'customer' => [
            ['Book and manage appointments', true],
            ['Payments and feedback', true],
            ['AI nail design workspace', true],
            ['Beauty preferences', true],
            ['Admin and staff operations', false],
            ['System settings', false],
        ],
        default => [],
    };
}

function accountCenterNotifications(string $role): array
{
    return match ($role) {
        'super_admin' => [
            'system_alerts' => 'System alerts',
            'admin_changes' => 'Administrator changes',
            'failed_logins' => 'Failed-login alerts',
            'backup_alerts' => 'Backup notifications',
            'ai_status' => 'AI service status',
            'payment_gateway' => 'Payment gateway status',
        ],
        'admin' => [
            'booking_alerts' => 'New booking alerts',
            'payment_alerts' => 'Payment verification alerts',
            'inventory_alerts' => 'Low inventory alerts',
            'feedback_alerts' => 'New feedback alerts',
            'schedule_alerts' => 'Schedule conflict alerts',
        ],
        'staff' => [
            'assignment_alerts' => 'New assignment alerts',
            'schedule_alerts' => 'Schedule changes',
            'reminder_alerts' => 'Appointment reminders',
            'performance_alerts' => 'Performance summaries',
        ],
        'customer' => [
            'booking_alerts' => 'Booking confirmations',
            'reminder_alerts' => 'Appointment reminders',
            'payment_alerts' => 'Payment updates',
            'promotion_alerts' => 'Promotions and offers',
        ],
        default => [],
    };
}

function accountCenterLog(
    PDO $pdo,
    int $userId,
    string $event,
    string $status = 'success'
): void {
    if (!accountCenterTableExists($pdo, 'account_login_history')) {
        return;
    }

    $statement = $pdo->prepare(
        "INSERT INTO account_login_history (
            user_id,
            event_type,
            status,
            ip_address,
            user_agent
        ) VALUES (?, ?, ?, ?, ?)"
    );

    $statement->execute([
        $userId,
        $event,
        $status,
        $_SERVER['REMOTE_ADDR'] ?? null,
        substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
    ]);
}
