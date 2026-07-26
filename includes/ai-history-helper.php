<?php
declare(strict_types=1);

function aiTableHasColumn(PDO $pdo, string $table, string $column): bool
{
    static $cache = [];
    $key = $table . '.' . $column;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    $statement = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.columns
         WHERE table_schema=DATABASE() AND table_name=? AND column_name=?"
    );
    $statement->execute([$table, $column]);
    return $cache[$key] = (bool) $statement->fetchColumn();
}

function aiLogUsage(
    PDO $pdo,
    int $customerId,
    string $event,
    ?int $requestId = null,
    ?int $designId = null,
    ?int $bookingId = null,
    ?string $provider = null
): void {
    try {
        $statement = $pdo->prepare(
            "INSERT INTO ai_usage_events
             (customer_id, request_id, event_type, design_id, booking_id, provider)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $statement->execute([
            $customerId, $requestId, $event, $designId, $bookingId, $provider
        ]);
    } catch (Throwable $exception) {
        // Usage analytics must never break generation or booking.
    }
}

function aiDesignForCustomer(PDO $pdo, int $designId, int $customerId): ?array
{
    $deletedClause = aiTableHasColumn($pdo, 'ai_generated_designs', 'deleted_at')
        ? ' AND d.deleted_at IS NULL' : '';
    $statement = $pdo->prepare(
        "SELECT d.*, r.nail_shape, r.nail_length, r.colors, r.design_style,
                r.occasion, r.add_ons, r.instructions, r.provider,
                r.model_name, r.created_at AS request_created_at
         FROM ai_generated_designs d
         INNER JOIN ai_design_requests r ON r.id=d.request_id
         WHERE d.id=? AND d.customer_id=? {$deletedClause}
         LIMIT 1"
    );
    $statement->execute([$designId, $customerId]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function aiAttachSelectedDesignToBooking(PDO $pdo, int $bookingId, int $customerId): void
{
    $designId = (int) ($_SESSION['selected_ai_design_id'] ?? 0);
    if ($designId < 1 || !aiTableHasColumn($pdo, 'bookings', 'selected_ai_design_id')) {
        return;
    }
    $design = aiDesignForCustomer($pdo, $designId, $customerId);
    if (!$design) {
        unset($_SESSION['selected_ai_design_id']);
        return;
    }
    $update = $pdo->prepare(
        "UPDATE bookings SET selected_ai_design_id=?
         WHERE id=? AND customer_id=?"
    );
    $update->execute([$designId, $bookingId, $customerId]);
    if ($update->rowCount()) {
        aiLogUsage(
            $pdo, $customerId, 'booked', (int) $design['request_id'],
            $designId, $bookingId, (string) $design['provider']
        );
        unset($_SESSION['selected_ai_design_id']);
    }
}

