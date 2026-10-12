<?php
declare(strict_types=1);

function bookingManagementJson(array $payload, int $status = 200): never
{
    global $pdo;
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function bookingManagementTableExists(PDO $pdo, string $table): bool
{
    static $cache = [];
    if (!array_key_exists($table, $cache)) {
        $statement = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?"
        );
        $statement->execute([$table]);
        $cache[$table] = (bool) $statement->fetchColumn();
    }
    return $cache[$table];
}

function bookingManagementColumnExists(
    PDO $pdo,
    string $table,
    string $column
): bool {
    static $cache = [];
    $key = $table . '.' . $column;
    if (!array_key_exists($key, $cache)) {
        $statement = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND column_name = ?"
        );
        $statement->execute([$table, $column]);
        $cache[$key] = (bool) $statement->fetchColumn();
    }
    return $cache[$key];
}

function bookingManagementAllowedStatuses(): array
{
    return [
        'pending',
        'confirmed',
        'in_progress',
        'completed',
        'cancelled',
        'no_show',
    ];
}

function bookingManagementTransitions(string $current): array
{
    return match ($current) {
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['in_progress', 'cancelled', 'no_show'],
        'in_progress' => ['completed', 'cancelled'],
        'completed', 'cancelled', 'no_show' => [],
        default => ['pending', 'confirmed', 'cancelled'],
    };
}

function bookingManagementStatusLabel(string $status): string
{
    return match ($status) {
        'in_progress' => 'In Progress',
        'no_show' => 'No Show',
        default => ucwords(str_replace('_', ' ', $status)),
    };
}

function bookingManagementReference(array $booking): string
{
    if (!empty($booking['booking_code'])) {
        return (string) $booking['booking_code'];
    }
    return 'NBW-' . str_pad((string) ($booking['id'] ?? 0), 6, '0', STR_PAD_LEFT);
}

function bookingManagementRecordHistory(
    PDO $pdo,
    int $bookingId,
    ?string $fromStatus,
    string $toStatus,
    ?int $actorId,
    ?string $note = null
): void {
    if (!bookingManagementTableExists($pdo, 'booking_status_history')) {
        return;
    }
    $statement = $pdo->prepare(
        "INSERT INTO booking_status_history
            (booking_id, from_status, to_status, changed_by, note)
         VALUES (?, ?, ?, ?, ?)"
    );
    $statement->execute([
        $bookingId,
        $fromStatus,
        $toStatus,
        $actorId,
        $note ?: null,
    ]);
}

function bookingManagementHistory(PDO $pdo, int $bookingId): array
{
    if (!bookingManagementTableExists($pdo, 'booking_status_history')) {
        return [];
    }
    $statement = $pdo->prepare(
        "SELECT h.*, u.fullname AS changed_by_name
         FROM booking_status_history h
         LEFT JOIN users u ON u.id = h.changed_by
         WHERE h.booking_id = ?
         ORDER BY h.created_at, h.id"
    );
    $statement->execute([$bookingId]);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function bookingManagementHasConflict(
    PDO $pdo,
    int $bookingId,
    int $branchId,
    int $staffId,
    string $date,
    string $time
): bool {
    $statement = $pdo->prepare(
        "SELECT COUNT(*) FROM bookings
         WHERE id <> ?
           AND branch_id = ?
           AND staff_id = ?
           AND booking_date = ?
           AND booking_time = ?
           AND status IN ('pending', 'confirmed', 'in_progress')"
    );
    $statement->execute([$bookingId, $branchId, $staffId, $date, $time]);
    return (int) $statement->fetchColumn() > 0;
}

function bookingManagementFetch(
    PDO $pdo,
    int $bookingId,
    int $branchId
): ?array {
    $hasBranches = bookingManagementTableExists($pdo, 'branches');
    $hasAiDesign = bookingManagementColumnExists(
        $pdo,
        'bookings',
        'selected_ai_design_id'
    );

    $selectBranch = $hasBranches
        ? "br.branch_name,"
        : "'Nails & Body Worx' AS branch_name,";
    $joinBranch = $hasBranches
        ? "LEFT JOIN branches br ON br.id = b.branch_id"
        : "";
    $selectAi = $hasAiDesign
        ? "b.selected_ai_design_id,"
        : "NULL AS selected_ai_design_id,";

    $statement = $pdo->prepare(
        "SELECT b.*,
                {$selectAi}
                {$selectBranch}
                c.fullname AS customer_name,
                c.email AS customer_email,
                c.phone_number AS customer_phone,
                s.name AS service_name,
                s.description AS service_description,
                s.price AS service_price,
                s.duration AS service_duration,
                st.fullname AS staff_name,
                p.id AS payment_id,
                p.amount AS payment_amount,
                p.payment_method,
                p.reference_number,
                p.proof_path,
                p.status AS payment_status
         FROM bookings b
         LEFT JOIN users c ON c.id = b.customer_id
         LEFT JOIN services s ON s.id = b.service_id
         LEFT JOIN users st ON st.id = b.staff_id
         LEFT JOIN payments p ON p.booking_id = b.id
         {$joinBranch}
         WHERE b.id = ? AND b.branch_id = ?
         LIMIT 1"
    );
    $statement->execute([$bookingId, $branchId]);
    $booking = $statement->fetch(PDO::FETCH_ASSOC);

    return $booking ?: null;
}
