<?php
declare(strict_types=1);

function reportTableExists(PDO $pdo, string $table): bool
{
    static $cache = [];
    if (!array_key_exists($table, $cache)) {
        $q = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?");
        $q->execute([$table]);
        $cache[$table] = (bool) $q->fetchColumn();
    }
    return $cache[$table];
}

function reportColumnExists(PDO $pdo, string $table, string $column): bool
{
    static $cache = [];
    $key = "{$table}.{$column}";
    if (!array_key_exists($key, $cache)) {
        $q = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=?");
        $q->execute([$table, $column]);
        $cache[$key] = (bool) $q->fetchColumn();
    }
    return $cache[$key];
}

function reportDateRange(): array
{
    $from = trim((string) ($_GET['from'] ?? date('Y-m-01')));
    $to = trim((string) ($_GET['to'] ?? date('Y-m-d')));
    $fromDate = DateTime::createFromFormat('Y-m-d', $from);
    $toDate = DateTime::createFromFormat('Y-m-d', $to);
    if (!$fromDate || $fromDate->format('Y-m-d') !== $from) $from = date('Y-m-01');
    if (!$toDate || $toDate->format('Y-m-d') !== $to) $to = date('Y-m-d');
    if ($from > $to) [$from, $to] = [$to, $from];
    return [$from, $to];
}

function reportMoney(float|int|string|null $amount): string
{
    return '₱' . number_format((float) $amount, 2);
}

function reportStatus(string $status): string
{
    $label = htmlspecialchars(ucwords(str_replace('_', ' ', $status)));
    $class = htmlspecialchars(preg_replace('/[^a-z_]/', '', strtolower($status)) ?: 'unknown');
    return "<span class=\"sb-status {$class}\">{$label}</span>";
}

function reportFetchAll(PDO $pdo, string $sql, array $params = []): array
{
    try {
        $q = $pdo->prepare($sql);
        foreach ($params as $name => $value) {
            $q->bindValue(':' . $name, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $q->execute();
        return $q->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $error) {
        error_log('SmartBooking report query failed: ' . $error->getMessage());
        return [];
    }
}

function reportFetchOne(PDO $pdo, string $sql, array $params = []): array
{
    return reportFetchAll($pdo, $sql, $params)[0] ?? [];
}

function reportBranchClause(?int $branchId, string $alias = 'b'): array
{
    return $branchId
        ? [" AND {$alias}.branch_id=:branch_id", ['branch_id' => $branchId]]
        : ['', []];
}

function buildSmartBookingReport(PDO $pdo, string $from, string $to, ?int $branchId, string $bookingStatus = 'all'): array
{
    [$branchSql, $branchParams] = reportBranchClause($branchId);
    $params = ['from' => $from, 'to' => $to] + $branchParams;
    $baseParams = $params;
    $statusSql = '';
    if (in_array($bookingStatus, ['pending','completed','cancelled'], true)) {
        $statusSql = ' AND b.status=:booking_status';
        $params['booking_status'] = $bookingStatus;
    }
    $pay = reportTableExists($pdo, 'payments')
        ? "(SELECT booking_id,
             SUM(CASE WHEN status='verified' THEN amount ELSE 0 END) verified_amount,
             SUM(CASE WHEN status='pending' THEN amount ELSE 0 END) pending_amount
           FROM payments GROUP BY booking_id) pay"
        : "(SELECT NULL booking_id,0 verified_amount,0 pending_amount) pay";

    $summary = reportFetchOne($pdo,
        "SELECT COUNT(*) bookings,SUM(b.status='completed') completed,
          SUM(b.status='cancelled') cancelled,COUNT(DISTINCT b.customer_id) customers,
          COALESCE(SUM(pay.verified_amount),0) revenue,
          COALESCE(SUM(pay.pending_amount),0) pending_payments
         FROM bookings b LEFT JOIN {$pay} ON pay.booking_id=b.id
         WHERE b.booking_date BETWEEN :from AND :to{$branchSql}{$statusSql}", $params);

    $daily = reportFetchAll($pdo,
        "SELECT b.booking_date label,COUNT(*) bookings,SUM(b.status='completed') completed,
          COALESCE(SUM(pay.verified_amount),0) revenue
         FROM bookings b LEFT JOIN {$pay} ON pay.booking_id=b.id
         WHERE b.booking_date BETWEEN :from AND :to{$branchSql}{$statusSql}
         GROUP BY b.booking_date ORDER BY b.booking_date", $params);

    $statuses = reportFetchAll($pdo,
        "SELECT b.status label,COUNT(*) value FROM bookings b
         WHERE b.booking_date BETWEEN :from AND :to{$branchSql}{$statusSql}
         GROUP BY b.status ORDER BY value DESC", $params);

    $services = reportFetchAll($pdo,
        "SELECT COALESCE(s.name,'Unknown service') label,COUNT(*) bookings,
          SUM(b.status='completed') completed,COALESCE(SUM(pay.verified_amount),0) revenue
         FROM bookings b LEFT JOIN services s ON s.id=b.service_id
         LEFT JOIN {$pay} ON pay.booking_id=b.id
         WHERE b.booking_date BETWEEN :from AND :to{$branchSql}{$statusSql}
         GROUP BY b.service_id,s.name ORDER BY bookings DESC,label LIMIT 20", $params);

    $staffBranch = $branchId && reportColumnExists($pdo, 'users', 'branch_id')
        ? " AND u.branch_id=:staff_branch_id" : '';
    $staffParams = ['from' => $from, 'to' => $to];
    $staffStatusSql = '';
    if (in_array($bookingStatus, ['pending','completed','cancelled'], true)) {$staffStatusSql=' AND b.status=:staff_booking_status';$staffParams['staff_booking_status']=$bookingStatus;}
    if ($branchId) {
        $staffParams['booking_branch_id'] = $branchId;
        if ($staffBranch) $staffParams['staff_branch_id'] = $branchId;
    }
    $staff = reportFetchAll($pdo,
        "SELECT u.id,u.fullname label,COUNT(b.id) assigned,SUM(b.status='completed') completed,
          COALESCE(SUM(pay.verified_amount),0) revenue
         FROM users u LEFT JOIN bookings b ON b.staff_id=u.id
           AND b.booking_date BETWEEN :from AND :to" . ($branchId ? " AND b.branch_id=:booking_branch_id" : '') . $staffStatusSql . "
         LEFT JOIN {$pay} ON pay.booking_id=b.id
         WHERE u.role='staff'{$staffBranch}
         GROUP BY u.id,u.fullname ORDER BY completed DESC,revenue DESC LIMIT 20", $staffParams);

    $bookings = reportFetchAll($pdo,
        "SELECT b.id,b.booking_date,b.booking_time,b.status,
          COALESCE(c.fullname,'Guest') customer,COALESCE(s.name,'Unknown service') service,
          COALESCE(st.fullname,'Not assigned') staff,COALESCE(br.branch_name,'Unassigned') branch,
          COALESCE(pay.verified_amount,0) verified_amount
         FROM bookings b LEFT JOIN users c ON c.id=b.customer_id
         LEFT JOIN users st ON st.id=b.staff_id LEFT JOIN services s ON s.id=b.service_id
         LEFT JOIN branches br ON br.id=b.branch_id LEFT JOIN {$pay} ON pay.booking_id=b.id
         WHERE b.booking_date BETWEEN :from AND :to{$branchSql}{$statusSql}
         ORDER BY b.booking_date DESC,b.booking_time DESC LIMIT 250", $params);

    $payments = reportTableExists($pdo, 'payments') ? reportFetchAll($pdo,
        "SELECT p.id,p.created_at,p.amount,p.payment_method,p.reference_number,p.status,
          b.id booking_id,COALESCE(c.fullname,'Guest') customer,COALESCE(br.branch_name,'Unassigned') branch
         FROM payments p LEFT JOIN bookings b ON b.id=p.booking_id
         LEFT JOIN users c ON c.id=b.customer_id LEFT JOIN branches br ON br.id=b.branch_id
         WHERE DATE(p.created_at) BETWEEN :from AND :to{$branchSql}
         ORDER BY p.created_at DESC LIMIT 250", $baseParams) : [];

    $feedback = reportTableExists($pdo, 'feedback') ? reportFetchAll($pdo,
        "SELECT f.id,f.rating,f.comment,f.status,f.created_at,
          COALESCE(c.fullname,'Customer') customer,COALESCE(s.name,'Unknown service') service,
          COALESCE(br.branch_name,'Unassigned') branch
         FROM feedback f LEFT JOIN bookings b ON b.id=f.booking_id
         LEFT JOIN users c ON c.id=f.customer_id LEFT JOIN services s ON s.id=b.service_id
         LEFT JOIN branches br ON br.id=b.branch_id
         WHERE DATE(f.created_at) BETWEEN :from AND :to{$branchSql}
         ORDER BY f.created_at DESC LIMIT 250", $baseParams) : [];

    $inventoryWhere = $branchId && reportColumnExists($pdo, 'inventory', 'branch_id')
        ? ' WHERE i.branch_id=:branch_id' : '';
    $inventory = reportTableExists($pdo, 'inventory') ? reportFetchAll($pdo,
        "SELECT i.id,i.item_name,i.quantity,i.reorder_level,i.unit,i.status,
          COALESCE(br.branch_name,'Unassigned') branch
         FROM inventory i LEFT JOIN branches br ON br.id=i.branch_id{$inventoryWhere}
         ORDER BY (i.quantity<=i.reorder_level) DESC,i.item_name LIMIT 250",
        $branchId ? ['branch_id' => $branchId] : []) : [];

    $inventorySummary = [
        'items' => count($inventory),
        'low' => count(array_filter($inventory, fn($r) => (float)$r['quantity'] <= (float)$r['reorder_level'] && (float)$r['quantity'] > 0)),
        'out' => count(array_filter($inventory, fn($r) => (float)$r['quantity'] <= 0)),
    ];
    $feedbackSummary = [
        'count' => count($feedback),
        'average' => $feedback ? array_sum(array_column($feedback, 'rating')) / count($feedback) : 0,
    ];
    return compact('summary','daily','statuses','services','staff','bookings','payments','feedback','inventory','inventorySummary','feedbackSummary');
}

function reportCsv(string $section, array $report, string $from, string $to): never
{
    $defs = [
        'bookings' => [['Booking #','Date','Time','Customer','Service','Staff','Branch','Status','Verified Revenue'], fn($r)=>[$r['id'],$r['booking_date'],$r['booking_time'],$r['customer'],$r['service'],$r['staff'],$r['branch'],$r['status'],$r['verified_amount']]],
        'services' => [['Service','Bookings','Completed','Verified Revenue'], fn($r)=>[$r['label'],$r['bookings'],$r['completed'],$r['revenue']]],
        'staff' => [['Staff','Assigned','Completed','Verified Revenue'], fn($r)=>[$r['label'],$r['assigned'],$r['completed'],$r['revenue']]],
        'payments' => [['Payment #','Date','Booking #','Customer','Branch','Method','Reference','Status','Amount'], fn($r)=>[$r['id'],$r['created_at'],$r['booking_id'],$r['customer'],$r['branch'],$r['payment_method'],$r['reference_number'],$r['status'],$r['amount']]],
        'feedback' => [['Date','Customer','Service','Branch','Rating','Status','Comment'], fn($r)=>[$r['created_at'],$r['customer'],$r['service'],$r['branch'],$r['rating'],$r['status'],$r['comment']]],
        'inventory' => [['Item','Branch','Quantity','Unit','Reorder Level','Status'], fn($r)=>[$r['item_name'],$r['branch'],$r['quantity'],$r['unit'],$r['reorder_level'],$r['status']]],
    ];
    if (!isset($defs[$section])) $section = 'bookings';
    header('Content-Type: text/csv; charset=UTF-8');
    header("Content-Disposition: attachment; filename=\"smartbooking-{$section}-{$from}-to-{$to}.csv\"");
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['SmartBooking ' . ucfirst($section) . ' Report']);
    fputcsv($out, ['Date range', "{$from} to {$to}"]);
    fputcsv($out, []);
    fputcsv($out, $defs[$section][0]);
    foreach ($report[$section] as $row) fputcsv($out, $defs[$section][1]($row));
    fclose($out);
    exit;
}
