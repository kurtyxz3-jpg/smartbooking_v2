<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/superadmin-analytics-helper.php';

$pageTitle = 'Business Reports';

[$startDate, $endDate] = saDateRange();
$reportType = $_GET['report_type'] ?? 'sales';

$allowedReports = [
    'sales',
    'bookings',
    'services',
    'inventory',
    'staff',
    'customers',
];

if (!in_array($reportType, $allowedReports, true)) {
    $reportType = 'sales';
}

$reportTitle = '';
$reportDescription = '';
$columns = [];
$rows = [];

switch ($reportType) {
    case 'bookings':
        $reportTitle = 'Bookings Report';
        $reportDescription = 'Booking activity within the selected date range.';
        $columns = ['Booking ID', 'Customer', 'Service', 'Staff', 'Date', 'Time', 'Status'];

        if (saTableExists($pdo, 'bookings')) {
            $statement = $pdo->prepare(
                "SELECT id, customer_id, service_id, staff_id, booking_date, booking_time, status
                 FROM bookings
                 WHERE booking_date BETWEEN ? AND ?
                 ORDER BY booking_date DESC, booking_time DESC"
            );
            $statement->execute([$startDate, $endDate]);

            foreach ($statement->fetchAll() as $row) {
                $rows[] = [
                    '#' . $row['id'],
                    $row['customer_id'] ?? '—',
                    $row['service_id'] ?? '—',
                    $row['staff_id'] ?? '—',
                    $row['booking_date'] ?? '—',
                    $row['booking_time'] ?? '—',
                    ucfirst(str_replace('_', ' ', $row['status'] ?? '—')),
                ];
            }
        }
        break;

    case 'services':
        $reportTitle = 'Services Report';
        $reportDescription = 'Service usage and completed service value.';
        $columns = ['Service', 'Completed Bookings', 'Service Price', 'Completed Value'];

        if (
            saTableExists($pdo, 'bookings') &&
            saTableExists($pdo, 'services') &&
            saColumnExists($pdo, 'services', 'price')
        ) {
            $statement = $pdo->prepare(
                "SELECT
                    s.name,
                    s.price,
                    COUNT(b.id) AS completed_count,
                    COALESCE(SUM(s.price), 0) AS completed_value
                 FROM services s
                 LEFT JOIN bookings b
                    ON b.service_id = s.id
                   AND b.status = 'completed'
                   AND b.booking_date BETWEEN ? AND ?
                 GROUP BY s.id, s.name, s.price
                 ORDER BY completed_value DESC, completed_count DESC"
            );
            $statement->execute([$startDate, $endDate]);

            foreach ($statement->fetchAll() as $row) {
                $rows[] = [
                    $row['name'],
                    $row['completed_count'],
                    saMoney((float) $row['price']),
                    saMoney((float) $row['completed_value']),
                ];
            }
        }
        break;

    case 'inventory':
        $reportTitle = 'Inventory Report';
        $reportDescription = 'Current stock levels and reorder conditions.';
        $columns = ['Item', 'Quantity', 'Unit', 'Reorder Level', 'Status'];

        if (saTableExists($pdo, 'inventory')) {
            $statement = $pdo->query(
                "SELECT item_name, quantity, unit, reorder_level, status
                 FROM inventory
                 ORDER BY item_name ASC"
            );

            foreach ($statement->fetchAll() as $row) {
                $rows[] = [
                    $row['item_name'] ?? '—',
                    $row['quantity'] ?? '0',
                    $row['unit'] ?? '—',
                    $row['reorder_level'] ?? '0',
                    ucfirst(str_replace('_', ' ', $row['status'] ?? '—')),
                ];
            }
        }
        break;

    case 'staff':
        $reportTitle = 'Staff Performance Report';
        $reportDescription = 'Completed services and generated service value by staff.';
        $columns = ['Staff Member', 'Completed Services', 'Service Value'];

        if (
            saTableExists($pdo, 'bookings') &&
            saTableExists($pdo, 'services') &&
            saTableExists($pdo, 'users')
        ) {
            $statement = $pdo->prepare(
                "SELECT
                    u.fullname,
                    COUNT(b.id) AS completed_count,
                    COALESCE(SUM(s.price), 0) AS service_value
                 FROM users u
                 LEFT JOIN bookings b
                    ON b.staff_id = u.id
                   AND b.status = 'completed'
                   AND b.booking_date BETWEEN ? AND ?
                 LEFT JOIN services s ON s.id = b.service_id
                 WHERE u.role = 'staff'
                 GROUP BY u.id, u.fullname
                 ORDER BY service_value DESC, completed_count DESC"
            );
            $statement->execute([$startDate, $endDate]);

            foreach ($statement->fetchAll() as $row) {
                $rows[] = [
                    $row['fullname'],
                    $row['completed_count'],
                    saMoney((float) $row['service_value']),
                ];
            }
        }
        break;

    case 'customers':
        $reportTitle = 'Customer Growth Report';
        $reportDescription = 'Customer accounts created within the selected date range.';
        $columns = ['Customer', 'Email', 'Status', 'Date Registered'];

        if (
            saTableExists($pdo, 'users') &&
            saColumnExists($pdo, 'users', 'created_at')
        ) {
            $statement = $pdo->prepare(
                "SELECT fullname, email, status, created_at
                 FROM users
                 WHERE role = 'customer'
                   AND DATE(created_at) BETWEEN ? AND ?
                 ORDER BY created_at DESC"
            );
            $statement->execute([$startDate, $endDate]);

            foreach ($statement->fetchAll() as $row) {
                $rows[] = [
                    $row['fullname'],
                    $row['email'] ?? '—',
                    ucfirst($row['status'] ?? '—'),
                    date('M d, Y', strtotime($row['created_at'])),
                ];
            }
        }
        break;

    case 'sales':
    default:
        $reportTitle = 'Sales Report';
        $reportDescription = 'Verified payments within the selected date range.';
        $columns = ['Payment ID', 'Booking', 'Amount', 'Method', 'Reference', 'Status', 'Date'];

        if (saTableExists($pdo, 'payments')) {
            $statement = $pdo->prepare(
                "SELECT id, booking_id, amount, payment_method, reference_number, status, created_at
                 FROM payments
                 WHERE DATE(created_at) BETWEEN ? AND ?
                 ORDER BY created_at DESC"
            );
            $statement->execute([$startDate, $endDate]);

            foreach ($statement->fetchAll() as $row) {
                $rows[] = [
                    '#' . $row['id'],
                    $row['booking_id'] ?? '—',
                    saMoney((float) $row['amount']),
                    $row['payment_method'] ?? '—',
                    $row['reference_number'] ?? '—',
                    ucfirst($row['status'] ?? '—'),
                    date('M d, Y h:i A', strtotime($row['created_at'])),
                ];
            }
        }
        break;
}

if (($_GET['export'] ?? '') === 'csv') {
    $filename = strtolower(str_replace(' ', '_', $reportTitle))
        . '_'
        . $startDate
        . '_to_'
        . $endDate
        . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    fputcsv($output, [$reportTitle]);
    fputcsv($output, ['Date Range', $startDate . ' to ' . $endDate]);
    fputcsv($output, []);
    fputcsv($output, $columns);

    foreach ($rows as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

include '../includes/superadmin-header.php';
?>

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>

    <main class="content">
        <div class="page-header analytics-page-header">
            <div>
                <h1 class="gold-title">Business Reports</h1>
                <p>Generate operational summaries for management review and thesis presentation.</p>
            </div>

            <a class="btn btn-light" href="sales-analytics.php">Sales Analytics</a>
        </div>

        <section class="card analytics-filter-card">
            <form class="reports-filter-form" method="GET">
                <div class="filter-field">
                    <label for="report_type">Report Type</label>
                    <select id="report_type" name="report_type">
                        <option value="sales" <?= $reportType === 'sales' ? 'selected' : '' ?>>Sales Report</option>
                        <option value="bookings" <?= $reportType === 'bookings' ? 'selected' : '' ?>>Bookings Report</option>
                        <option value="services" <?= $reportType === 'services' ? 'selected' : '' ?>>Services Report</option>
                        <option value="inventory" <?= $reportType === 'inventory' ? 'selected' : '' ?>>Inventory Report</option>
                        <option value="staff" <?= $reportType === 'staff' ? 'selected' : '' ?>>Staff Performance Report</option>
                        <option value="customers" <?= $reportType === 'customers' ? 'selected' : '' ?>>Customer Growth Report</option>
                    </select>
                </div>

                <div class="filter-field">
                    <label for="start_date">Start Date</label>
                    <input
                        id="start_date"
                        name="start_date"
                        type="date"
                        value="<?= htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="filter-field">
                    <label for="end_date">End Date</label>
                    <input
                        id="end_date"
                        name="end_date"
                        type="date"
                        value="<?= htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="reports-filter-actions">
                    <button class="btn btn-gold" type="submit">Generate Report</button>
                    <a class="btn btn-light" href="reports.php">Reset</a>
                </div>
            </form>
        </section>

        <section class="card report-card">
            <div class="report-heading">
                <div>
                    <span>SmartBooking V2</span>
                    <h2><?= htmlspecialchars($reportTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($reportDescription, ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="report-actions">
                    <a
                        class="btn btn-light"
                        href="?<?= http_build_query([
                            'report_type' => $reportType,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'export' => 'csv',
                        ]) ?>"
                    >
                        Export CSV
                    </a>

                    <button class="btn btn-gold" type="button" onclick="window.print();">
                        Print Report
                    </button>
                </div>
            </div>

            <div class="report-meta">
                <div>
                    <span>Date Range</span>
                    <strong>
                        <?= date('M d, Y', strtotime($startDate)) ?>
                        —
                        <?= date('M d, Y', strtotime($endDate)) ?>
                    </strong>
                </div>

                <div>
                    <span>Total Records</span>
                    <strong><?= count($rows) ?></strong>
                </div>

                <div>
                    <span>Generated</span>
                    <strong><?= date('M d, Y h:i A') ?></strong>
                </div>
            </div>

            <div class="table-wrap report-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <?php foreach ($columns as $column): ?>
                                <th>
                                    <?= htmlspecialchars($column, ENT_QUOTES, 'UTF-8') ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="<?= count($columns) ?>" class="analytics-empty">
                                No report records are available for the selected criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td>
                                        <?= htmlspecialchars(
                                            (string) $value,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
