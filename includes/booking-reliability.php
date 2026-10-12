<?php
declare(strict_types=1);
require_once __DIR__ . '/staff-schedule-helper.php';
date_default_timezone_set((require __DIR__ . '/../config/reliability.php')['timezone']);

// Every booking writer acquires this row BEFORE reading availability. InnoDB
// releases it on commit, rollback or a lost connection. One salon, one mutex.
function bookingWriteBegin(PDO $pdo): void
{
    if ($pdo->inTransaction()) {
        throw new LogicException('Booking transaction already open.');
    }
    $pdo->exec('SET time_zone=' . $pdo->quote((new DateTimeImmutable())->format('P')));
    $pdo->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
    $pdo->beginTransaction();
    $lock = $pdo->query('SELECT id FROM booking_write_mutex WHERE id = 1 FOR UPDATE');
    if (!$lock->fetchColumn()) {
        $pdo->rollBack();
        throw new RuntimeException('Booking setup is incomplete.');
    }
}

function bookingChooseStaff(PDO $pdo, int $staffId, int $branchId, string $date, string $time, int $duration, int $excludeId = 0): int
{
    if (!$pdo->inTransaction()) {
        throw new LogicException('Availability must be reserved in a transaction.');
    }
    if ($staffId > 0) {
        $result = staffScheduleAvailability($pdo, $staffId, $branchId, $date, $time, $duration, $excludeId);
        if (!$result['available']) {
            throw new DomainException('Choose another appointment time. ' . $result['reason']);
        }
        return $staffId;
    }
    $staff = $pdo->prepare("SELECT id FROM users WHERE role='staff' AND status='active' AND branch_id=? ORDER BY id");
    $staff->execute([$branchId]);
    foreach ($staff->fetchAll(PDO::FETCH_COLUMN) as $id) {
        $result = staffScheduleAvailability($pdo, (int)$id, $branchId, $date, $time, $duration, $excludeId);
        if ($result['available']) return (int)$id;
    }
    throw new DomainException('No staff member is available for the whole appointment. Choose another time.');
}

function bookingSavedDuration(PDO $pdo, array $booking): int
{
    if (!empty($booking['estimated_duration'])) return max(15, (int)$booking['estimated_duration']);
    $s = $pdo->prepare('SELECT duration FROM services WHERE id=?');
    $s->execute([$booking['service_id']]);
    return max(15, (int)($s->fetchColumn() ?: 60));
}
