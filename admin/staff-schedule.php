<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/premium-admin-helper.php';
require_once '../includes/branch-context.php';
require_once '../includes/staff-schedule-helper.php';

$pageTitle = 'Staff Schedule & Availability';
$branchId = requireAssignedBranch($pdo);
$selectedStaffId = filter_input(INPUT_GET, 'staff_id', FILTER_VALIDATE_INT) ?: 0;

$staffStatement = $pdo->prepare(
    "SELECT id, fullname, email, profile_picture
     FROM users
     WHERE role = 'staff' AND branch_id = ? AND status = 'active'
     ORDER BY fullname"
);
$staffStatement->execute([$branchId]);
$staffMembers = $staffStatement->fetchAll(PDO::FETCH_ASSOC);

$validIds = array_map(static fn(array $row): int => (int) $row['id'], $staffMembers);
if (!in_array($selectedStaffId, $validIds, true)) {
    $selectedStaffId = $validIds[0] ?? 0;
}

$weekly = $selectedStaffId
    ? staffScheduleWeekly($pdo, $selectedStaffId, $branchId)
    : [];
$exceptions = $selectedStaffId
    ? staffScheduleExceptions(
        $pdo,
        $selectedStaffId,
        $branchId,
        date('Y-m-d'),
        date('Y-m-d', strtotime('+90 days'))
    )
    : [];
$selectedStaff = null;
foreach ($staffMembers as $staff) {
    if ((int) $staff['id'] === $selectedStaffId) {
        $selectedStaff = $staff;
        break;
    }
}

include '../includes/admin-header.php';
?>
<link rel="stylesheet" href="../assets/css/staff-schedule-availability.css?v=093">

<div class="admin-layout">
    <?php include '../includes/admin-sidebar.php'; ?>
    <main class="admin-content schedule-admin-page">
        <header class="admin-page-header schedule-page-header">
            <div>
                <p class="schedule-kicker">Team operations</p>
                <h1>Staff Schedule &amp; Availability</h1>
                <p>Set weekly working hours, breaks, days off, and temporary unavailability for this branch.</p>
            </div>
            <a class="admin-btn admin-btn-light" href="staff.php">Back to Staff</a>
        </header>

        <section class="schedule-shell">
            <aside class="schedule-staff-panel">
                <div class="schedule-panel-heading">
                    <p class="schedule-kicker">Branch staff</p>
                    <h2>Select a professional</h2>
                </div>
                <label class="schedule-search">
                    <span>Search</span>
                    <input id="scheduleStaffSearch" type="search" placeholder="Name or email">
                </label>
                <div class="schedule-staff-list" id="scheduleStaffList">
                    <?php if (!$staffMembers): ?>
                        <div class="schedule-empty">No active staff are assigned to this branch.</div>
                    <?php endif; ?>
                    <?php foreach ($staffMembers as $staff): ?>
                        <a
                            class="schedule-staff-card <?= (int)$staff['id'] === $selectedStaffId ? 'active' : '' ?>"
                            href="?staff_id=<?= (int)$staff['id'] ?>"
                            data-staff-search="<?= htmlspecialchars(strtolower($staff['fullname'] . ' ' . $staff['email'])) ?>"
                        >
                            <span class="schedule-avatar">
                                <?= htmlspecialchars(strtoupper(substr((string)$staff['fullname'], 0, 1))) ?>
                            </span>
                            <span>
                                <strong><?= htmlspecialchars($staff['fullname']) ?></strong>
                                <small><?= htmlspecialchars($staff['email']) ?></small>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>

            <div class="schedule-workspace">
                <?php if (!$selectedStaff): ?>
                    <section class="schedule-card schedule-empty">
                        Add or activate a staff account before creating a schedule.
                    </section>
                <?php else: ?>
                    <section class="schedule-card schedule-person-bar">
                        <div class="schedule-avatar large">
                            <?= htmlspecialchars(strtoupper(substr((string)$selectedStaff['fullname'], 0, 1))) ?>
                        </div>
                        <div>
                            <p class="schedule-kicker">Editing schedule for</p>
                            <h2><?= htmlspecialchars($selectedStaff['fullname']) ?></h2>
                            <p><?= htmlspecialchars($selectedStaff['email']) ?></p>
                        </div>
                        <span class="schedule-status-pill">Active staff</span>
                    </section>

                    <form id="weeklyScheduleForm" class="schedule-card">
                        <input type="hidden" name="csrf_token" value="<?= premiumAdminCsrf() ?>">
                        <input type="hidden" name="staff_id" value="<?= $selectedStaffId ?>">
                        <div class="schedule-section-heading">
                            <div>
                                <p class="schedule-kicker">Regular availability</p>
                                <h2>Weekly working hours</h2>
                                <p>Turn a day off or define the work and break periods.</p>
                            </div>
                            <button class="admin-btn admin-btn-primary" type="submit">Save Weekly Schedule</button>
                        </div>
                        <div class="weekly-grid">
                            <?php foreach ($weekly as $day => $row): ?>
                                <article class="weekly-day <?= (int)$row['is_working'] ? '' : 'is-off' ?>" data-weekly-day>
                                    <header>
                                        <div>
                                            <strong><?= htmlspecialchars($row['day_name']) ?></strong>
                                            <small data-day-state><?= (int)$row['is_working'] ? 'Working day' : 'Day off' ?></small>
                                        </div>
                                        <label class="schedule-switch">
                                            <input
                                                type="checkbox"
                                                name="days[<?= $day ?>][is_working]"
                                                value="1"
                                                <?= (int)$row['is_working'] ? 'checked' : '' ?>
                                                data-working-toggle
                                            >
                                            <span></span>
                                        </label>
                                    </header>
                                    <div class="weekly-time-grid">
                                        <label>Starts<input type="time" name="days[<?= $day ?>][start_time]" value="<?= htmlspecialchars($row['start_time']) ?>"></label>
                                        <label>Ends<input type="time" name="days[<?= $day ?>][end_time]" value="<?= htmlspecialchars($row['end_time']) ?>"></label>
                                        <label>Break starts<input type="time" name="days[<?= $day ?>][break_start]" value="<?= htmlspecialchars($row['break_start']) ?>"></label>
                                        <label>Break ends<input type="time" name="days[<?= $day ?>][break_end]" value="<?= htmlspecialchars($row['break_end']) ?>"></label>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </form>

                    <section class="schedule-card">
                        <div class="schedule-section-heading">
                            <div>
                                <p class="schedule-kicker">Schedule exceptions</p>
                                <h2>Days off &amp; temporary unavailability</h2>
                                <p>Add a full day off, unavailable period, or custom working hours.</p>
                            </div>
                            <button
                                class="admin-btn admin-btn-primary"
                                type="button"
                                id="openExceptionForm"
                                aria-controls="exceptionForm"
                                aria-expanded="false"
                            >
                                Add Exception
                            </button>
                        </div>

                        <form id="exceptionForm" class="exception-form" hidden>
                            <input type="hidden" name="csrf_token" value="<?= premiumAdminCsrf() ?>">
                            <input type="hidden" name="staff_id" value="<?= $selectedStaffId ?>">
                            <label>Date<input type="date" name="exception_date" min="<?= date('Y-m-d') ?>" required></label>
                            <label>Type
                                <select name="exception_type" id="exceptionType">
                                    <option value="day_off">Full day off</option>
                                    <option value="unavailable">Unavailable period</option>
                                    <option value="custom_hours">Custom working hours</option>
                                </select>
                            </label>
                            <label data-exception-time>Starts<input type="time" name="start_time" value="09:00"></label>
                            <label data-exception-time>Ends<input type="time" name="end_time" value="12:00"></label>
                             <label class="exception-reason">Reason<input name="reason" maxlength="255" placeholder="Leave, training, personal appointment..."></label>
                            <div
                                class="schedule-form-message"
                                data-exception-message
                                role="status"
                                hidden
                            ></div>
                            <div class="exception-actions">
                                <button type="button" class="admin-btn admin-btn-light" id="cancelException">Cancel</button>
                                <button class="admin-btn admin-btn-primary" type="submit">Save Exception</button>
                            </div>
                        </form>

                        <div class="exception-list" id="exceptionList">
                            <?php if (!$exceptions): ?>
                                <div class="schedule-empty">No upcoming schedule exceptions.</div>
                            <?php endif; ?>
                            <?php foreach ($exceptions as $exception): ?>
                                <article class="exception-row">
                                    <div class="exception-date">
                                        <span><?= date('M', strtotime($exception['exception_date'])) ?></span>
                                        <strong><?= date('d', strtotime($exception['exception_date'])) ?></strong>
                                    </div>
                                    <div>
                                        <strong><?= ucwords(str_replace('_', ' ', $exception['exception_type'])) ?></strong>
                                        <p>
                                            <?php if ($exception['exception_type'] !== 'day_off'): ?>
                                                <?= date('g:i A', strtotime($exception['start_time'])) ?>
                                                – <?= date('g:i A', strtotime($exception['end_time'])) ?>
                                                ·
                                            <?php endif; ?>
                                            <?= htmlspecialchars($exception['reason'] ?: 'No reason provided') ?>
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        class="schedule-delete-exception"
                                        data-delete-exception="<?= (int)$exception['id'] ?>"
                                    >Remove</button>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </section>

        <script>
        window.NBW_SCHEDULE_ENDPOINT = 'ajax/staff-schedule.php';
        </script>
        <script src="../assets/js/staff-schedule-availability.js?v=093"></script>
        <?php include '../includes/admin-footer.php'; ?>
    </main>
</div>
