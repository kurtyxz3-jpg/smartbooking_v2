<?php
declare(strict_types=1);

function staffScheduleTableExists(PDO $pdo, string $table): bool
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

function staffScheduleDayNames(): array
{
    return [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];
}

function staffScheduleDefaults(): array
{
    $rows = [];
    foreach (staffScheduleDayNames() as $number => $name) {
        $rows[$number] = [
            'day_of_week' => $number,
            'day_name' => $name,
            'is_working' => $number < 7 ? 1 : 0,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => '12:00',
            'break_end' => '13:00',
        ];
    }
    return $rows;
}

function staffScheduleWeekly(PDO $pdo, int $staffId, int $branchId): array
{
    $rows = staffScheduleDefaults();
    if (!staffScheduleTableExists($pdo, 'staff_weekly_availability')) {
        return $rows;
    }
    $statement = $pdo->prepare(
        "SELECT day_of_week, is_working,
                TIME_FORMAT(start_time, '%H:%i') AS start_time,
                TIME_FORMAT(end_time, '%H:%i') AS end_time,
                TIME_FORMAT(break_start, '%H:%i') AS break_start,
                TIME_FORMAT(break_end, '%H:%i') AS break_end
         FROM staff_weekly_availability
         WHERE staff_id = ? AND branch_id = ?"
    );
    $statement->execute([$staffId, $branchId]);
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $day = (int) $row['day_of_week'];
        if (isset($rows[$day])) {
            $rows[$day] = array_merge($rows[$day], $row);
        }
    }
    return $rows;
}

function staffScheduleExceptions(
    PDO $pdo,
    int $staffId,
    int $branchId,
    ?string $from = null,
    ?string $to = null
): array {
    if (!staffScheduleTableExists($pdo, 'staff_schedule_exceptions')) {
        return [];
    }
    $sql =
        "SELECT id, exception_date, exception_type,
                TIME_FORMAT(start_time, '%H:%i') AS start_time,
                TIME_FORMAT(end_time, '%H:%i') AS end_time,
                reason
         FROM staff_schedule_exceptions
         WHERE staff_id = ? AND branch_id = ?";
    $params = [$staffId, $branchId];
    if ($from !== null) {
        $sql .= " AND exception_date >= ?";
        $params[] = $from;
    }
    if ($to !== null) {
        $sql .= " AND exception_date <= ?";
        $params[] = $to;
    }
    $sql .= " ORDER BY exception_date, start_time";
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function staffScheduleMinutes(string $time): int
{
    [$hour, $minute] = array_map('intval', explode(':', substr($time, 0, 5)));
    return ($hour * 60) + $minute;
}

function staffScheduleOverlaps(
    int $startA,
    int $endA,
    int $startB,
    int $endB
): bool {
    return $startA < $endB && $endA > $startB;
}

function staffScheduleAvailability(
    PDO $pdo,
    int $staffId,
    int $branchId,
    string $date,
    string $time,
    int $durationMinutes = 60,
    int $excludeBookingId = 0
): array {
    if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::00)?$/', $time)) {
        return ['available' => false, 'reason' => 'Invalid time.'];
    }
    $staffCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id=? AND role='staff' AND status='active' AND branch_id=?");
    $staffCheck->execute([$staffId, $branchId]);
    if (!$staffCheck->fetchColumn()) return ['available'=>false, 'reason'=>'Staff is not available at the salon.'];
    $dateObject = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
        return ['available' => false, 'reason' => 'Invalid date.'];
    }

    $weekly = staffScheduleWeekly($pdo, $staffId, $branchId);
    $day = (int) $dateObject->format('N');
    $rule = $weekly[$day] ?? null;
    if (!$rule) return ['available'=>false,'reason'=>'No schedule configured.'];
    $working = (bool)$rule['is_working'];

    $requestStart = staffScheduleMinutes($time);
    $requestEnd = $requestStart + max(15, $durationMinutes);
    $workStart = staffScheduleMinutes((string) ($rule['start_time'] ?? '00:00'));
    $workEnd = staffScheduleMinutes((string) ($rule['end_time'] ?? '00:00'));

    $exceptions = staffScheduleExceptions(
        $pdo,
        $staffId,
        $branchId,
        $date,
        $date
    );
    foreach ($exceptions as $exception) {
        if ($exception['exception_type'] === 'day_off') {
            return [
                'available' => false,
                'reason' => $exception['reason'] ?: 'Staff is unavailable on this date.',
            ];
        }
        if ($exception['exception_type'] === 'custom_hours') {
            $working = true;
            $workStart = staffScheduleMinutes((string) $exception['start_time']);
            $workEnd = staffScheduleMinutes((string) $exception['end_time']);
        }
        if (
            $exception['exception_type'] === 'unavailable'
            && staffScheduleOverlaps(
                $requestStart,
                $requestEnd,
                staffScheduleMinutes((string) $exception['start_time']),
                staffScheduleMinutes((string) $exception['end_time'])
            )
        ) {
            return [
                'available' => false,
                'reason' => $exception['reason'] ?: 'Staff is temporarily unavailable.',
            ];
        }
    }

    if (!$working) return ['available'=>false,'reason'=>'Staff is off on this day.'];

    if ($requestStart < $workStart || $requestEnd > $workEnd) {
        return ['available' => false, 'reason' => 'Outside working hours.'];
    }

    if (
        !empty($rule['break_start'])
        && !empty($rule['break_end'])
        && staffScheduleOverlaps(
            $requestStart,
            $requestEnd,
            staffScheduleMinutes((string) $rule['break_start']),
            staffScheduleMinutes((string) $rule['break_end'])
        )
    ) {
        return ['available' => false, 'reason' => 'This time overlaps a staff break.'];
    }

    $sql =
        "SELECT b.id,
                TIME_FORMAT(b.booking_time, '%H:%i') AS booking_time,
                COALESCE(NULLIF(b.estimated_duration, 0), s.duration, 60) AS duration_minutes
         FROM bookings b
         LEFT JOIN services s ON s.id = b.service_id
         WHERE (b.staff_id = ? OR b.staff_id IS NULL)
           AND b.branch_id = ?
           AND b.booking_date = ?
           AND b.status IN ('pending', 'confirmed', 'in_progress')";
    $params = [$staffId, $branchId, $date];
    if ($excludeBookingId > 0) {
        $sql .= " AND b.id <> ?";
        $params[] = $excludeBookingId;
    }
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $booking) {
        $bookingStart = staffScheduleMinutes((string) $booking['booking_time']);
        $bookingEnd = $bookingStart + max(15, (int) $booking['duration_minutes']);
        if (staffScheduleOverlaps($requestStart, $requestEnd, $bookingStart, $bookingEnd)) {
            return ['available' => false, 'reason' => 'Staff already has a booking.'];
        }
    }

    return ['available' => true, 'reason' => 'Available'];
}

function staffScheduleAvailableStaff(
    PDO $pdo,
    int $branchId,
    string $date,
    string $time,
    int $durationMinutes = 60
): array {
    $statement = $pdo->prepare(
        "SELECT id, fullname FROM users
         WHERE role = 'staff' AND status = 'active' AND branch_id = ?
         ORDER BY fullname"
    );
    $statement->execute([$branchId]);
    $available = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $staff) {
        $result = staffScheduleAvailability(
            $pdo,
            (int) $staff['id'],
            $branchId,
            $date,
            $time,
            $durationMinutes
        );
        if ($result['available']) {
            $available[] = [
                'id' => (int) $staff['id'],
                'name' => (string) $staff['fullname'],
            ];
        }
    }
    return $available;
}
