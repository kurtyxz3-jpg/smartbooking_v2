<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';
require_once '../includes/premium-admin-helper.php';
require_once '../includes/branch-context.php';
require_once '../includes/booking-management-helper.php';

$pageTitle = 'Booking Management';
$branchId = requireAssignedBranch($pdo);
$csrfToken = premiumAdminCsrf();

$search = trim((string) ($_GET['search'] ?? ''));
$status = trim((string) ($_GET['status'] ?? 'all'));
$dateFrom = trim((string) ($_GET['date_from'] ?? ''));
$dateTo = trim((string) ($_GET['date_to'] ?? ''));
$staffFilter = filter_input(INPUT_GET, 'staff_id', FILTER_VALIDATE_INT) ?: null;
$view = ($_GET['view'] ?? 'table') === 'calendar' ? 'calendar' : 'table';

$where = ['b.branch_id = :branch_scope'];
$params = ['branch_scope' => $branchId];

if ($search !== '') {
    $needle = '%' . $search . '%';
    $where[] = "(
        CAST(b.id AS CHAR) LIKE :search_id
        OR c.fullname LIKE :search_customer
        OR c.email LIKE :search_email
        OR s.name LIKE :search_service
        OR st.fullname LIKE :search_staff
    )";
    $params['search_id'] = $needle;
    $params['search_customer'] = $needle;
    $params['search_email'] = $needle;
    $params['search_service'] = $needle;
    $params['search_staff'] = $needle;
}

if ($status !== 'all' && in_array($status, bookingManagementAllowedStatuses(), true)) {
    $where[] = 'b.status = :status';
    $params['status'] = $status;
}
if ($dateFrom !== '') {
    $where[] = 'b.booking_date >= :date_from';
    $params['date_from'] = $dateFrom;
}
if ($dateTo !== '') {
    $where[] = 'b.booking_date <= :date_to';
    $params['date_to'] = $dateTo;
}
if ($staffFilter) {
    $where[] = 'b.staff_id = :staff_id';
    $params['staff_id'] = $staffFilter;
}

$whereSql = implode(' AND ', $where);
$hasBranches = bookingManagementTableExists($pdo, 'branches');
$branchSelect = $hasBranches
    ? 'br.branch_name'
    : "'Current Branch' AS branch_name";
$branchJoin = $hasBranches
    ? 'LEFT JOIN branches br ON br.id = b.branch_id'
    : '';

$statement = $pdo->prepare(
    "SELECT b.*,
            {$branchSelect},
            c.fullname AS customer_name,
            c.email AS customer_email,
            s.name AS service_name,
            s.price AS service_price,
            s.duration AS service_duration,
            st.fullname AS staff_name,
            p.status AS payment_status
     FROM bookings b
     LEFT JOIN users c ON c.id = b.customer_id
     LEFT JOIN services s ON s.id = b.service_id
     LEFT JOIN users st ON st.id = b.staff_id
     LEFT JOIN payments p ON p.booking_id = b.id
     {$branchJoin}
     WHERE {$whereSql}
     ORDER BY b.booking_date DESC, b.booking_time DESC, b.id DESC
     LIMIT 250"
);
premiumAdminBind($statement, $params);
$statement->execute();
$bookings = $statement->fetchAll(PDO::FETCH_ASSOC);

$staffStatement = $pdo->prepare(
    "SELECT id, fullname FROM users
     WHERE role = 'staff' AND status = 'active' AND branch_id = ?
     ORDER BY fullname"
);
$staffStatement->execute([$branchId]);
$staffMembers = $staffStatement->fetchAll(PDO::FETCH_ASSOC);

$summaryStatement = $pdo->prepare(
    "SELECT
        COUNT(*) AS total,
        SUM(status = 'pending') AS pending,
        SUM(status = 'confirmed') AS confirmed,
        SUM(status = 'in_progress') AS in_progress,
        SUM(status = 'completed') AS completed,
        SUM(status = 'cancelled') AS cancelled,
        SUM(status = 'no_show') AS no_show,
        SUM(booking_date = CURRENT_DATE
            AND status NOT IN ('cancelled', 'no_show')) AS today
     FROM bookings
     WHERE branch_id = ?"
);
$summaryStatement->execute([$branchId]);
$summary = $summaryStatement->fetch(PDO::FETCH_ASSOC) ?: [];

include '../includes/admin-header.php';
?>
<link rel="stylesheet" href="../assets/css/booking-management.css">

<div class="admin-layout">
    <?php include '../includes/admin-sidebar.php'; ?>

    <main class="admin-content booking-management-page">
        <header class="admin-page-header booking-page-header">
            <div>
                <p class="booking-kicker">Appointment operations</p>
                <h1>Booking Management</h1>
                <p>
                    Review appointments, assign staff, and move every visit
                    through a clear service workflow.
                </p>
            </div>
            <div class="booking-header-meta">
                <span><?= htmlspecialchars(branchName($pdo, $branchId)) ?></span>
                <strong><?= date('l, F j') ?></strong>
            </div>
        </header>

        <section class="booking-summary-grid" aria-label="Booking overview">
            <?php
            $cards = [
                ['today', 'Today', (int) ($summary['today'] ?? 0)],
                ['pending', 'Pending', (int) ($summary['pending'] ?? 0)],
                ['confirmed', 'Confirmed', (int) ($summary['confirmed'] ?? 0)],
                ['in_progress', 'In Progress', (int) ($summary['in_progress'] ?? 0)],
                ['completed', 'Completed', (int) ($summary['completed'] ?? 0)],
            ];
            foreach ($cards as [$cardStatus, $label, $value]):
            ?>
                <article class="booking-summary-card <?= $cardStatus ?>">
                    <span class="booking-summary-dot"></span>
                    <div>
                        <small><?= $label ?></small>
                        <strong data-summary="<?= $cardStatus ?>"><?= $value ?></strong>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="admin-card booking-toolbar">
            <div class="booking-toolbar-title">
                <div>
                    <p class="booking-kicker">Find an appointment</p>
                    <h2>Booking queue</h2>
                </div>
                <div class="booking-view-toggle" role="group" aria-label="Choose view">
                    <button type="button" data-booking-view="table"
                        class="<?= $view === 'table' ? 'active' : '' ?>">Table</button>
                    <button type="button" data-booking-view="calendar"
                        class="<?= $view === 'calendar' ? 'active' : '' ?>">Calendar</button>
                </div>
            </div>

            <form method="GET" class="booking-filter-form" id="bookingFilters">
                <input type="hidden" name="view" value="<?= $view ?>" id="bookingViewInput">
                <label class="booking-search-field">
                    <span>Search</span>
                    <input
                        type="search"
                        name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Customer, service, staff, or booking number"
                        data-booking-live-search
                    >
                </label>
                <label>
                    <span>Status</span>
                    <select name="status">
                        <option value="all">All statuses</option>
                        <?php foreach (bookingManagementAllowedStatuses() as $option): ?>
                            <option value="<?= $option ?>" <?= $status === $option ? 'selected' : '' ?>>
                                <?= bookingManagementStatusLabel($option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>From</span>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
                </label>
                <label>
                    <span>To</span>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
                </label>
                <label>
                    <span>Staff</span>
                    <select name="staff_id">
                        <option value="">All staff</option>
                        <?php foreach ($staffMembers as $member): ?>
                            <option value="<?= (int) $member['id'] ?>"
                                <?= $staffFilter === (int) $member['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($member['fullname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="booking-filter-actions">
                    <button class="booking-button primary" type="submit">Apply</button>
                    <a class="booking-button quiet" href="bookings.php">Reset</a>
                </div>
            </form>
        </section>

        <div class="booking-result-line">
            <span><strong id="bookingVisibleCount"><?= count($bookings) ?></strong> bookings shown</span>
            <span>Click any booking to review its full details.</span>
        </div>

        <section class="admin-card booking-table-view"
            data-booking-panel="table" <?= $view !== 'table' ? 'hidden' : '' ?>>
            <div class="booking-table-scroll">
                <table class="booking-table">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Customer &amp; Service</th>
                            <th>Schedule</th>
                            <th>Staff</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="bookingRows">
                        <?php if (!$bookings): ?>
                            <tr class="booking-server-empty">
                                <td colspan="7">
                                    <div class="booking-empty-state">
                                        <strong>No bookings found</strong>
                                        <span>Try adjusting the filters or date range.</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                            $searchText = strtolower(implode(' ', [
                                $booking['id'],
                                $booking['customer_name'] ?? '',
                                $booking['customer_email'] ?? '',
                                $booking['service_name'] ?? '',
                                $booking['staff_name'] ?? '',
                                $booking['status'] ?? '',
                            ]));
                            ?>
                            <tr
                                data-booking-row
                                data-booking-id="<?= (int) $booking['id'] ?>"
                                data-search="<?= htmlspecialchars($searchText) ?>"
                            >
                                <td>
                                    <strong><?= htmlspecialchars(bookingManagementReference($booking)) ?></strong>
                                    <small><?= htmlspecialchars($booking['branch_name']) ?></small>
                                </td>
                                <td class="booking-primary-cell">
                                    <strong><?= htmlspecialchars($booking['customer_name'] ?? 'Guest customer') ?></strong>
                                    <small>
                                        <?= htmlspecialchars($booking['service_name'] ?? 'Unknown service') ?>
                                        · <?= premiumAdminMoney($booking['service_price'] ?? 0) ?>
                                    </small>
                                </td>
                                <td>
                                    <strong><?= date('M j, Y', strtotime($booking['booking_date'])) ?></strong>
                                    <small><?= date('g:i A', strtotime($booking['booking_time'])) ?></small>
                                </td>
                                <td><?= htmlspecialchars($booking['staff_name'] ?? 'Unassigned') ?></td>
                                <td>
                                    <span class="booking-payment-status <?= htmlspecialchars($booking['payment_status'] ?? 'pending') ?>">
                                        <?= premiumAdminStatus($booking['payment_status'] ?? 'not_submitted') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="booking-status <?= htmlspecialchars($booking['status']) ?>">
                                        <?= bookingManagementStatusLabel((string) $booking['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="booking-manage-button" type="button"
                                        data-manage-booking="<?= (int) $booking['id'] ?>">
                                        Manage
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="bookingClientEmpty" hidden>
                            <td colspan="7">
                                <div class="booking-empty-state">
                                    <strong>No matching booking</strong>
                                    <span>Clear the search or try another keyword.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card booking-calendar-view"
            data-booking-panel="calendar" <?= $view !== 'calendar' ? 'hidden' : '' ?>>
            <div class="booking-calendar-head">
                <div>
                    <p class="booking-kicker">Schedule overview</p>
                    <h2>Calendar view</h2>
                </div>
                <div>
                    <button type="button" data-calendar-shift="-1">Previous</button>
                    <strong id="bookingCalendarTitle"></strong>
                    <button type="button" data-calendar-shift="1">Next</button>
                </div>
            </div>
            <div class="booking-calendar-weekdays" aria-hidden="true">
                <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span>
                <span>Thu</span><span>Fri</span><span>Sat</span>
            </div>
            <div class="booking-calendar-grid" id="bookingCalendar"></div>
        </section>

        <?php include '../includes/admin-footer.php'; ?>
    </main>
</div>

<div class="booking-modal" id="bookingModal" aria-hidden="true">
    <div class="booking-modal-backdrop" data-booking-close></div>
    <section class="booking-modal-dialog" role="dialog" aria-modal="true"
        aria-labelledby="bookingModalTitle">
        <header>
            <div>
                <p class="booking-kicker">Appointment details</p>
                <h2 id="bookingModalTitle">Booking</h2>
            </div>
            <button class="booking-modal-close" type="button" data-booking-close
                aria-label="Close booking details">&times;</button>
        </header>

        <div class="booking-modal-loading" data-booking-loading>
            <span></span> Loading booking details…
        </div>

        <div class="booking-modal-content" data-booking-content hidden>
            <div class="booking-detail-grid">
                <div class="booking-detail-column">
                    <section class="booking-detail-card">
                        <h3>Customer &amp; service</h3>
                        <dl>
                            <div><dt>Customer</dt><dd data-detail="customer_name"></dd></div>
                            <div><dt>Email</dt><dd data-detail="customer_email"></dd></div>
                            <div><dt>Phone</dt><dd data-detail="customer_phone"></dd></div>
                            <div><dt>Service</dt><dd data-detail="service_name"></dd></div>
                            <div><dt>Branch</dt><dd data-detail="branch_name"></dd></div>
                            <div><dt>Schedule</dt><dd data-detail="schedule"></dd></div>
                            <div><dt>Notes</dt><dd data-detail="notes"></dd></div>
                        </dl>
                    </section>

                    <section class="booking-detail-card">
                        <h3>Payment</h3>
                        <dl>
                            <div><dt>Status</dt><dd data-detail="payment_status"></dd></div>
                            <div><dt>Amount</dt><dd data-detail="payment_amount"></dd></div>
                            <div><dt>Method</dt><dd data-detail="payment_method"></dd></div>
                            <div><dt>Reference</dt><dd data-detail="reference_number"></dd></div>
                        </dl>
                    </section>
                </div>

                <aside class="booking-detail-column">
                    <section class="booking-detail-card booking-workflow-card">
                        <h3>Manage appointment</h3>
                        <form id="bookingUpdateForm">
                            <input type="hidden" name="csrf_token"
                                value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="booking_id" data-booking-id-input>
                            <label>
                                <span>Assigned staff</span>
                                <select name="staff_id" data-booking-staff>
                                    <option value="">Unassigned</option>
                                    <?php foreach ($staffMembers as $member): ?>
                                        <option value="<?= (int) $member['id'] ?>">
                                            <?= htmlspecialchars($member['fullname']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span>Next status</span>
                                <select name="status" data-booking-status></select>
                            </label>
                            <label>
                                <span>Internal note</span>
                                <textarea name="note" rows="3"
                                    placeholder="Optional reason or handoff note"></textarea>
                            </label>
                            <div class="booking-form-message" data-booking-message hidden></div>
                            <button class="booking-button primary" type="submit">
                                Save changes
                            </button>
                        </form>
                    </section>

                    <section class="booking-detail-card">
                        <h3>Booking timeline</h3>
                        <ol class="booking-timeline" data-booking-timeline></ol>
                    </section>
                </aside>
            </div>
        </div>
    </section>
</div>

<script>
window.SmartBookingManagement = <?= json_encode([
    'detailsUrl' => 'ajax/booking-details.php',
    'updateUrl' => 'ajax/update-booking.php',
    'bookings' => array_map(static fn(array $booking): array => [
        'id' => (int) $booking['id'],
        'reference' => bookingManagementReference($booking),
        'customer' => (string) ($booking['customer_name'] ?? 'Guest customer'),
        'service' => (string) ($booking['service_name'] ?? 'Salon service'),
        'staff' => (string) ($booking['staff_name'] ?? 'Unassigned'),
        'date' => (string) $booking['booking_date'],
        'time' => (string) $booking['booking_time'],
        'status' => (string) $booking['status'],
    ], $bookings),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="../assets/js/booking-management.js"></script>
