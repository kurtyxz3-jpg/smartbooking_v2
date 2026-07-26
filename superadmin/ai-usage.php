<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/audit-helper.php';

$pageTitle = 'AI Usage';
$message = '';
$error = '';
if (empty($_SESSION['ai_usage_csrf'])) {
    $_SESSION['ai_usage_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(
        (string) $_SESSION['ai_usage_csrf'],
        (string) ($_POST['csrf_token'] ?? '')
    )) {
        $error = 'Session validation failed. Refresh the page and try again.';
    } else {
        $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
        $reason = mb_substr(trim((string) ($_POST['reason'] ?? '')), 0, 255);
        if (!$customerId) {
            $error = 'Select a valid customer.';
        } else {
            try {
                $insert = $pdo->prepare(
                    "INSERT INTO ai_daily_limit_resets
                     (customer_id, reset_by, reason) VALUES (?, ?, ?)"
                );
                $insert->execute([
                    $customerId,
                    (int) $_SESSION['user_id'],
                    $reason ?: 'Manual reset by Super Administrator',
                ]);
                writeAuditLog(
                    $pdo,
                    'Reset customer AI daily limit',
                    'AI Usage',
                    'AI daily limit was reset for customer ID ' . $customerId . '.'
                );
                $message = 'The customer can generate designs again today.';
            } catch (Throwable $exception) {
                $error = $exception->getMessage();
            }
        }
    }
}

$summary = [
    'requests_today' => 0, 'completed_today' => 0, 'failed_today' => 0,
    'selected_today' => 0, 'booked_total' => 0, 'customers_today' => 0,
];
try {
    $summary = $pdo->query(
        "SELECT
          COUNT(*) AS requests_today,
          SUM(status='completed') AS completed_today,
          SUM(status='failed') AS failed_today,
          COUNT(DISTINCT customer_id) AS customers_today,
          (SELECT COUNT(*) FROM ai_usage_events
           WHERE event_type='selected' AND created_at>=CURDATE()) AS selected_today,
          (SELECT COUNT(*) FROM ai_usage_events
           WHERE event_type='booked') AS booked_total
         FROM ai_design_requests WHERE created_at>=CURDATE()"
    )->fetch(PDO::FETCH_ASSOC) ?: $summary;
} catch (Throwable $exception) {
    $error = $error ?: 'Import database/066_ai_history_booking.sql first.';
}

$customers = [];
try {
    $customers = $pdo->query(
        "SELECT u.id, u.fullname, u.email,
                COUNT(r.id) AS total_requests,
                SUM(r.created_at>=CURDATE()) AS today_requests,
                MAX(r.created_at) AS last_request
         FROM users u
         LEFT JOIN ai_design_requests r ON r.customer_id=u.id
         WHERE u.role='customer'
         GROUP BY u.id, u.fullname, u.email
         ORDER BY today_requests DESC, total_requests DESC, u.fullname"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $exception) {
    $customers = [];
}

$recent = [];
try {
    $recent = $pdo->query(
        "SELECT e.*, u.fullname
         FROM ai_usage_events e
         LEFT JOIN users u ON u.id=e.customer_id
         ORDER BY e.created_at DESC LIMIT 30"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $exception) {
    $recent = [];
}

include '../includes/superadmin-header.php';
?>
<link rel="stylesheet" href="../assets/css/ai-usage.css">
<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>
    <main class="content">
        <div class="page-header">
            <div><h1 class="gold-title">AI Usage</h1>
            <p>Monitor generation activity, selections, booking conversions, and customer limits.</p></div>
            <a class="btn btn-light" href="ai-settings.php">AI Settings</a>
        </div>
        <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <section class="ai-usage-kpis">
            <?php
            $cards = [
                ['Requests today', $summary['requests_today']],
                ['Completed today', $summary['completed_today']],
                ['Failed today', $summary['failed_today']],
                ['Customers today', $summary['customers_today']],
                ['Selections today', $summary['selected_today']],
                ['Booked designs', $summary['booked_total']],
            ];
            foreach ($cards as [$label, $value]): ?>
                <article><span><?= htmlspecialchars($label) ?></span>
                <strong><?= (int) $value ?></strong></article>
            <?php endforeach; ?>
        </section>

        <section class="card ai-usage-section">
            <div><h2>Customer usage and limit reset</h2>
            <p>A reset starts a fresh daily allowance without deleting history.</p></div>
            <div class="table-wrap">
                <table><thead><tr><th>Customer</th><th>Today</th><th>Total</th>
                <th>Last request</th><th>Reset allowance</th></tr></thead><tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr><td><strong><?= htmlspecialchars($customer['fullname']) ?></strong><br>
                        <small><?= htmlspecialchars($customer['email']) ?></small></td>
                    <td><?= (int) $customer['today_requests'] ?></td>
                    <td><?= (int) $customer['total_requests'] ?></td>
                    <td><?= $customer['last_request'] ? date('M d, Y g:i A', strtotime($customer['last_request'])) : '—' ?></td>
                    <td><form method="post" class="ai-reset-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['ai_usage_csrf']) ?>">
                        <input type="hidden" name="customer_id" value="<?= (int) $customer['id'] ?>">
                        <input name="reason" maxlength="255" placeholder="Optional reason">
                        <button class="btn btn-gold" type="submit">Reset</button>
                    </form></td></tr>
                <?php endforeach; ?>
                </tbody></table>
            </div>
        </section>

        <section class="card ai-usage-section">
            <div><h2>Recent AI activity</h2><p>Latest generation and customer actions.</p></div>
            <div class="table-wrap"><table><thead><tr><th>Time</th><th>Customer</th>
            <th>Event</th><th>Provider</th><th>Request</th></tr></thead><tbody>
            <?php if (!$recent): ?><tr><td colspan="5">No activity recorded yet.</td></tr><?php endif; ?>
            <?php foreach ($recent as $event): ?><tr>
                <td><?= date('M d, g:i A', strtotime($event['created_at'])) ?></td>
                <td><?= htmlspecialchars($event['fullname'] ?: 'Customer #' . $event['customer_id']) ?></td>
                <td><span class="ai-event"><?= htmlspecialchars(ucfirst($event['event_type'])) ?></span></td>
                <td><?= htmlspecialchars($event['provider'] ?: '—') ?></td>
                <td><?= $event['request_id'] ? '#' . (int) $event['request_id'] : '—' ?></td>
            </tr><?php endforeach; ?></tbody></table></div>
        </section>
        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>

