<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';
require_once '../includes/premium-admin-helper.php';
require_once '../includes/branch-context.php';

$pageTitle='Reports';
[$from,$to]=premiumAdminDateRange();
$branchId=requireAssignedBranch($pdo);

$summary=$pdo->prepare(
 "SELECT
   COUNT(DISTINCT b.id) AS booking_count,
   SUM(b.status='completed') AS completed_count,
   COUNT(DISTINCT b.customer_id) AS customer_count,
   COALESCE(SUM(CASE WHEN p.status='verified' THEN p.amount ELSE 0 END),0) AS revenue
  FROM bookings b
  LEFT JOIN payments p ON p.booking_id=b.id
  WHERE b.booking_date BETWEEN ? AND ? AND b.branch_id=?"
);
$summary->execute([$from,$to,$branchId]);$stats=$summary->fetch(PDO::FETCH_ASSOC);

$services=$pdo->prepare(
 "SELECT s.name,COUNT(b.id) AS bookings,
   COALESCE(SUM(CASE WHEN p.status='verified' THEN p.amount ELSE 0 END),0) AS revenue
  FROM bookings b
  LEFT JOIN services s ON s.id=b.service_id
  LEFT JOIN payments p ON p.booking_id=b.id
  WHERE b.booking_date BETWEEN ? AND ? AND b.branch_id=?
  GROUP BY s.id,s.name ORDER BY bookings DESC LIMIT 8"
);
$services->execute([$from,$to,$branchId]);$serviceRows=$services->fetchAll(PDO::FETCH_ASSOC);

$daily=$pdo->prepare(
 "SELECT b.booking_date,COUNT(DISTINCT b.id) AS bookings,
   COALESCE(SUM(CASE WHEN p.status='verified' THEN p.amount ELSE 0 END),0) AS revenue
  FROM bookings b
  LEFT JOIN payments p ON p.booking_id=b.id
  WHERE b.booking_date BETWEEN ? AND ? AND b.branch_id=?
  GROUP BY b.booking_date ORDER BY b.booking_date"
);
$daily->execute([$from,$to,$branchId]);$dailyRows=$daily->fetchAll(PDO::FETCH_ASSOC);

if(($_GET['export']??'')==='csv'){
 header('Content-Type:text/csv');
 header('Content-Disposition:attachment; filename="smartbooking-report-'.$from.'-to-'.$to.'.csv"');
 $stream=fopen('php://output','w');
 fputcsv($stream,['Date','Bookings','Verified Revenue']);
 foreach($dailyRows as $row)fputcsv($stream,[$row['booking_date'],$row['bookings'],$row['revenue']]);
 fclose($stream);exit;
}

$maxDaily=max(1,...array_map(fn($r)=>(float)$r['revenue'],$dailyRows?:[['revenue'=>0]]));
$maxService=max(1,...array_map(fn($r)=>(int)$r['bookings'],$serviceRows?:[['bookings'=>0]]));

include '../includes/admin-header.php';
?>
<link rel="stylesheet" href="../assets/css/premium-admin-suite.css">
<div class="admin-layout"><?php include '../includes/admin-sidebar.php';?><main class="admin-content">
<header class="admin-page-header premium-admin-header"><div><p class="premium-admin-kicker">Business intelligence</p><h1>Reports</h1><p>Verified revenue, booking performance, customers, and service demand.</p></div><div class="premium-admin-header-actions"><a class="admin-btn admin-btn-light" href="?from=<?=urlencode($from)?>&to=<?=urlencode($to)?>&export=csv">Export CSV</a><button class="admin-btn admin-btn-primary" onclick="window.print()">Print / Save PDF</button></div></header>
<form class="admin-card premium-admin-toolbar"><div class="premium-admin-toolbar-head"><div><p class="premium-admin-kicker">Reporting period</p><h2>Select a date range</h2><p>Only verified payments count as confirmed revenue.</p></div></div><div class="premium-admin-filter compact"><div class="admin-field"><label>From</label><input type="date" name="from" value="<?=htmlspecialchars($from)?>"></div><div class="admin-field"><label>To</label><input type="date" name="to" value="<?=htmlspecialchars($to)?>"></div><div class="premium-admin-filter-actions"><button class="admin-btn admin-btn-primary">Generate Report</button><a class="admin-btn admin-btn-light" href="reports.php">Current Month</a></div></div></form>
<section class="premium-admin-stat-grid"><article><small>Verified revenue</small><strong><?=premiumAdminMoney($stats['revenue'])?></strong><span>Selected period</span></article><article><small>Total bookings</small><strong><?=(int)$stats['booking_count']?></strong><span>All booking statuses</span></article><article><small>Completed</small><strong><?=(int)$stats['completed_count']?></strong><span>Completed services</span></article><article><small>Customers</small><strong><?=(int)$stats['customer_count']?></strong><span>Unique customers</span></article></section>
<section class="premium-admin-chart-grid"><article class="admin-card premium-admin-chart"><p class="premium-admin-kicker">Revenue trend</p><h2>Verified revenue by day</h2><div class="premium-admin-bars"><?php if(!$dailyRows):?><div class="premium-admin-empty"><span>◇</span><h2>No report data</h2></div><?php endif;?><?php foreach($dailyRows as $row):?><div class="premium-admin-bar"><i style="height:<?=max(3,((float)$row['revenue']/$maxDaily)*100)?>%" title="<?=premiumAdminMoney($row['revenue'])?>"></i><span><?=date('M j',strtotime($row['booking_date']))?></span></div><?php endforeach;?></div></article><article class="admin-card premium-admin-chart"><p class="premium-admin-kicker">Service demand</p><h2>Most-booked services</h2><div class="premium-admin-bars"><?php foreach($serviceRows as $row):?><div class="premium-admin-bar"><i style="height:<?=max(3,((int)$row['bookings']/$maxService)*100)?>%" title="<?=(int)$row['bookings']?> bookings"></i><span><?=htmlspecialchars(mb_strimwidth($row['name']??'Unknown',0,12,'…'))?></span></div><?php endforeach;?></div></article></section>
<section class="admin-card premium-admin-table-card" style="margin-top:15px"><div class="admin-table-wrap"><table class="premium-admin-table"><thead><tr><th>Service</th><th>Bookings</th><th>Verified Revenue</th></tr></thead><tbody><?php foreach($serviceRows as $row):?><tr><td><?=htmlspecialchars($row['name']??'Unknown service')?></td><td><?=(int)$row['bookings']?></td><td><?=premiumAdminMoney($row['revenue'])?></td></tr><?php endforeach;?></tbody></table></div></section>
<?php include '../includes/admin-footer.php';?></main></div>
