<?php
declare(strict_types=1);

/**
 * Loads the currently signed-in user's display information for headers
 * and sidebars. The session values remain as a safe fallback.
 */
function currentAccountDisplay(?PDO $pdo = null): array
{
    $display = [
        'id' => (int) ($_SESSION['user_id'] ?? 0),
        'fullname' => trim((string) ($_SESSION['fullname'] ?? 'Account User')),
        'role' => (string) ($_SESSION['role'] ?? ''),
        'profile_picture' => null,
    ];

    if (!$pdo || $display['id'] <= 0) {
        return $display;
    }

    try {
        $statement = $pdo->prepare(
            "SELECT fullname, role, profile_picture
             FROM users
             WHERE id = ?
             LIMIT 1"
        );
        $statement->execute([$display['id']]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $display['fullname'] =
                trim((string) ($row['fullname'] ?? $display['fullname']));
            $display['role'] =
                (string) ($row['role'] ?? $display['role']);
            $display['profile_picture'] =
                !empty($row['profile_picture'])
                    ? basename((string) $row['profile_picture'])
                    : null;
        }
    } catch (Throwable $exception) {
        // Keep the session fallback so layout rendering never fails.
    }

    return $display;
}

function currentAccountInitials(string $fullname): string
{
    $parts = preg_split('/\s+/', trim($fullname)) ?: [];
    $initials = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        if ($part !== '') {
            $initials .= strtoupper(substr($part, 0, 1));
        }
    }

    return $initials !== '' ? $initials : 'NB';
}

function currentAccountPhotoUrl(array $display): ?string
{
    if (empty($display['profile_picture'])) {
        return null;
    }

    return '../uploads/profile/'
        . rawurlencode(basename((string) $display['profile_picture']));
}
