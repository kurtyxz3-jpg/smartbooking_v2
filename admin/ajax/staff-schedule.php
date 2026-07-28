<?php
declare(strict_types=1);

require_once '../../includes/admin-auth.php';
require_once '../../config/database.php';
require_once '../../includes/premium-admin-helper.php';
require_once '../../includes/branch-context.php';
require_once '../../includes/staff-schedule-helper.php';

header('Content-Type: application/json; charset=utf-8');

function scheduleApiResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$branchId = requireAssignedBranch($pdo);
$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}
if (!premiumAdminVerifyCsrf($input['csrf_token'] ?? null)) {
    scheduleApiResponse(['success' => false, 'message' => 'Session token expired.'], 419);
}

$staffId = filter_var($input['staff_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$staffCheck = $pdo->prepare(
    "SELECT COUNT(*) FROM users
     WHERE id = ? AND role = 'staff' AND status = 'active' AND branch_id = ?"
);
$staffCheck->execute([$staffId, $branchId]);
if (!$staffCheck->fetchColumn()) {
    scheduleApiResponse(['success' => false, 'message' => 'Staff member was not found in this branch.'], 404);
}

$action = (string) ($input['action'] ?? '');
try {
    if ($action === 'save_weekly') {
        $days = $input['days'] ?? [];
        if (!is_array($days)) {
            scheduleApiResponse(['success' => false, 'message' => 'Invalid weekly schedule.'], 422);
        }
        $normalizedDays = [];
        foreach (staffScheduleDayNames() as $day => $dayName) {
            $row = is_array($days[$day] ?? null) ? $days[$day] : [];
            $working = !empty($row['is_working']) ? 1 : 0;
            $start = preg_match('/^\d{2}:\d{2}$/', (string)($row['start_time'] ?? ''))
                ? $row['start_time'] : null;
            $end = preg_match('/^\d{2}:\d{2}$/', (string)($row['end_time'] ?? ''))
                ? $row['end_time'] : null;
            $breakStart = preg_match('/^\d{2}:\d{2}$/', (string)($row['break_start'] ?? ''))
                ? $row['break_start'] : null;
            $breakEnd = preg_match('/^\d{2}:\d{2}$/', (string)($row['break_end'] ?? ''))
                ? $row['break_end'] : null;
            if ($working && (!$start || !$end || $start >= $end)) {
                throw new RuntimeException("{$dayName} needs valid start and end times.");
            }
            if ($working && $breakStart && $breakEnd && $breakStart >= $breakEnd) {
                throw new RuntimeException("{$dayName} has an invalid break period.");
            }
            $normalizedDays[$day] = compact(
                'working', 'start', 'end', 'breakStart', 'breakEnd'
            );
        }

        $futureBookings = $pdo->prepare(
            "SELECT b.booking_date,
                    TIME_FORMAT(b.booking_time, '%H:%i') AS booking_time,
                    COALESCE(s.duration, 60) AS duration_minutes
             FROM bookings b
             LEFT JOIN services s ON s.id = b.service_id
             WHERE b.staff_id = ? AND b.branch_id = ?
               AND b.booking_date >= CURDATE()
               AND b.status IN ('pending','confirmed','in_progress')"
        );
        $futureBookings->execute([$staffId, $branchId]);
        foreach ($futureBookings->fetchAll(PDO::FETCH_ASSOC) as $booking) {
            $bookingDay = (int) date('N', strtotime($booking['booking_date']));
            $rule = $normalizedDays[$bookingDay];
            $bookingStart = staffScheduleMinutes($booking['booking_time']);
            $bookingEnd = $bookingStart + max(15, (int)$booking['duration_minutes']);
            $conflict = !$rule['working']
                || $bookingStart < staffScheduleMinutes($rule['start'])
                || $bookingEnd > staffScheduleMinutes($rule['end'])
                || (
                    $rule['breakStart']
                    && $rule['breakEnd']
                    && staffScheduleOverlaps(
                        $bookingStart,
                        $bookingEnd,
                        staffScheduleMinutes($rule['breakStart']),
                        staffScheduleMinutes($rule['breakEnd'])
                    )
                );
            if ($conflict) {
                throw new RuntimeException(
                    'The proposed hours conflict with an existing booking on '
                    . date('M j, Y', strtotime($booking['booking_date']))
                    . '. Reschedule that booking first.'
                );
            }
        }

        $pdo->beginTransaction();
        $statement = $pdo->prepare(
            "INSERT INTO staff_weekly_availability
                (staff_id, branch_id, day_of_week, is_working,
                 start_time, end_time, break_start, break_end)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                is_working = VALUES(is_working),
                start_time = VALUES(start_time),
                end_time = VALUES(end_time),
                break_start = VALUES(break_start),
                break_end = VALUES(break_end)"
        );
        foreach ($normalizedDays as $day => $row) {
            $statement->execute([
                $staffId, $branchId, $day, $row['working'],
                $row['start'], $row['end'],
                $row['breakStart'], $row['breakEnd'],
            ]);
        }
        $pdo->commit();
        scheduleApiResponse(['success' => true, 'message' => 'Weekly schedule saved.']);
    }

    if ($action === 'add_exception') {
        $date = (string) ($input['exception_date'] ?? '');
        $type = (string) ($input['exception_type'] ?? '');
        $start = (string) ($input['start_time'] ?? '');
        $end = (string) ($input['end_time'] ?? '');
        $reason = trim((string) ($input['reason'] ?? ''));
        $validTypes = ['day_off', 'unavailable', 'custom_hours'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < date('Y-m-d')) {
            scheduleApiResponse(['success' => false, 'message' => 'Choose a valid current or future date.'], 422);
        }
        if (!in_array($type, $validTypes, true)) {
            scheduleApiResponse(['success' => false, 'message' => 'Choose a valid exception type.'], 422);
        }
        if ($type === 'day_off') {
            $start = null;
            $end = null;
        } elseif (
            !preg_match('/^\d{2}:\d{2}$/', $start)
            || !preg_match('/^\d{2}:\d{2}$/', $end)
            || $start >= $end
        ) {
            scheduleApiResponse(['success' => false, 'message' => 'Enter a valid start and end time.'], 422);
        }

        if ($type !== 'day_off') {
            $bookingCheck = $pdo->prepare(
                "SELECT COUNT(*) FROM bookings
                 WHERE staff_id = ? AND branch_id = ? AND booking_date = ?
                   AND status IN ('pending','confirmed','in_progress')
                   AND booking_time >= ? AND booking_time < ?"
            );
            $bookingCheck->execute([$staffId, $branchId, $date, $start, $end]);
            if ((int) $bookingCheck->fetchColumn() > 0) {
                scheduleApiResponse([
                    'success' => false,
                    'message' => 'This exception conflicts with an existing booking. Reschedule the booking first.',
                ], 409);
            }
        } else {
            $bookingCheck = $pdo->prepare(
                "SELECT COUNT(*) FROM bookings
                 WHERE staff_id = ? AND branch_id = ? AND booking_date = ?
                   AND status IN ('pending','confirmed','in_progress')"
            );
            $bookingCheck->execute([$staffId, $branchId, $date]);
            if ((int) $bookingCheck->fetchColumn() > 0) {
                scheduleApiResponse([
                    'success' => false,
                    'message' => 'This day has existing bookings. Reschedule them before marking the full day off.',
                ], 409);
            }
        }

        $statement = $pdo->prepare(
            "INSERT INTO staff_schedule_exceptions
                (staff_id, branch_id, exception_date, exception_type,
                 start_time, end_time, reason, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $statement->execute([
            $staffId, $branchId, $date, $type,
            $start, $end, $reason ?: null, (int)$_SESSION['user_id'],
        ]);
        scheduleApiResponse(['success' => true, 'message' => 'Schedule exception added.']);
    }

    if ($action === 'delete_exception') {
        $exceptionId = filter_var($input['exception_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $statement = $pdo->prepare(
            "DELETE FROM staff_schedule_exceptions
             WHERE id = ? AND staff_id = ? AND branch_id = ?"
        );
        $statement->execute([$exceptionId, $staffId, $branchId]);
        scheduleApiResponse(['success' => true, 'message' => 'Schedule exception removed.']);
    }

    scheduleApiResponse(['success' => false, 'message' => 'Unsupported action.'], 400);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    scheduleApiResponse([
        'success' => false,
        'message' => $exception instanceof RuntimeException
            ? $exception->getMessage()
            : 'The schedule could not be saved.',
    ], 500);
}
