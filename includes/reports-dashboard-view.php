<?php
declare(strict_types=1);
$isSuperAdmin = ($reportContext ?? 'admin') === 'superadmin';
$baseQuery = http_build_query(['from'=>$from,'to'=>$to,'branch_id'=>$isSuperAdmin ? ($branchId ?? '') : null]);
$s = $report['summary'];
$tables = [
 'bookings'=>['Booking','Date & Time','Customer','Service','Staff','Branch','Status','Revenue'],
 'services'=>['Service','Bookings','Completed','Verified Revenue'],
 'staff'=>['Staff Member','Assigned','Completed','Completion Rate','Verified Revenue'],
 'payments'=>['Payment','Date','Customer','Branch','Method','Reference','Status','Amount'],
 'feedback'=>['Date','Customer','Service','Branch','Rating','Status','Comment'],
 'inventory'=>['Item','Branch','Quantity','Reorder Level','Status'],
];
?>
<link rel="stylesheet" href="../assets/css/reports-dashboard.css">
<div class="sb-report-shell">
 <header class="sb-report-header">
  <div><p class="sb-report-kicker"><?= $isSuperAdmin?'All-branch intelligence':'Branch performance' ?></p><h1>Reports Center</h1><p>Revenue, bookings, services, staff, payments, feedback, and inventory in one view.</p></div>
  <div class="sb-report-actions no-print"><button type="button" class="sb-report-btn secondary" data-print-report>Print / Save PDF</button><div class="sb-export-menu"><button type="button" class="sb-report-btn primary" data-export-toggle>Export CSV</button><div class="sb-export-popover" hidden><?php foreach(array_keys($tables) as $sct):?><a href="?<?=htmlspecialchars($baseQuery)?>&export=<?=$sct?>"><?=ucfirst($sct)?></a><?php endforeach;?></div></div></div>
 </header>
 <form class="sb-report-filter no-print" method="get">
  <div><label for="from">From</label><input id="from" type="date" name="from" value="<?=htmlspecialchars($from)?>"></div>
  <div><label for="to">To</label><input id="to" type="date" name="to" value="<?=htmlspecialchars($to)?>"></div>
  <?php if($isSuperAdmin):?><div class="wide"><label for="branch_id">Branch</label><select id="branch_id" name="branch_id"><option value="">All Branches</option><?php foreach($branches as $b):?><option value="<?=(int)$b['id']?>" <?=$branchId===(int)$b['id']?'selected':''?>><?=htmlspecialchars($b['branch_name'])?></option><?php endforeach;?></select></div>
  <?php else:?><div class="sb-filter-branch"><span>Branch</span><strong><?=htmlspecialchars($branchLabel)?></strong></div><?php endif;?>
  <button class="sb-report-btn primary" type="submit">Apply Filters</button><a class="sb-report-btn secondary" href="reports.php">Reset</a>
 </form>
 <section class="sb-report-metrics">
  <article><span>Verified Revenue</span><strong><?=reportMoney($s['revenue']??0)?></strong><small>Selected period</small></article>
  <article><span>Total Bookings</span><strong><?=(int)($s['bookings']??0)?></strong><small><?=(int)($s['completed']??0)?> completed</small></article>
  <article><span>Unique Customers</span><strong><?=(int)($s['customers']??0)?></strong><small>Customers served</small></article>
  <article><span>Average Rating</span><strong><?=number_format((float)$report['feedbackSummary']['average'],1)?>/5</strong><small><?=(int)$report['feedbackSummary']['count']?> responses</small></article>
  <article><span>Stock Alerts</span><strong><?=(int)$report['inventorySummary']['low']+(int)$report['inventorySummary']['out']?></strong><small><?=(int)$report['inventorySummary']['out']?> out of stock</small></article>
 </section>
 <section class="sb-report-chart-grid">
  <article class="sb-report-card"><div class="sb-card-heading"><div><span>Trend</span><h2>Revenue & bookings</h2></div></div><div class="sb-chart-wrap"><canvas id="sbRevenueChart"></canvas></div></article>
  <article class="sb-report-card"><div class="sb-card-heading"><div><span>Distribution</span><h2>Booking status</h2></div></div><div class="sb-donut-layout"><div class="sb-donut" id="sbStatusDonut"><span><?=(int)($s['bookings']??0)?><small>Bookings</small></span></div><div class="sb-donut-legend" id="sbStatusLegend"></div></div></article>
 </section>
 <nav class="sb-report-tabs no-print"><?php foreach(['bookings'=>'Bookings','services'=>'Services','staff'=>'Staff Performance','payments'=>'Payments','feedback'=>'Feedback','inventory'=>'Inventory'] as $key=>$label):?><button type="button" data-report-tab="<?=$key?>" class="<?=$key==='bookings'?'active':''?>"><?=$label?></button><?php endforeach;?></nav>

 <?php foreach(array_keys($tables) as $section):?>
 <section class="sb-report-card sb-detail-section <?=$section==='bookings'?'active':''?>" data-report-panel="<?=$section?>">
  <div class="sb-card-heading"><div><span>Detailed report</span><h2><?=ucwords(str_replace('_',' ',$section))?></h2></div><em><?=count($report[$section])?> records</em></div>
  <div class="sb-table-scroll"><table><thead><tr><?php foreach($tables[$section] as $heading):?><th><?=$heading?></th><?php endforeach;?></tr></thead><tbody>
  <?php foreach($report[$section] as $row):?><tr>
   <?php if($section==='bookings'):?><td>#<?=(int)$row['id']?></td><td><?=htmlspecialchars(date('M j, Y',strtotime($row['booking_date'])))?><small><?=htmlspecialchars(date('g:i A',strtotime($row['booking_time'])))?></small></td><td><?=htmlspecialchars($row['customer'])?></td><td><?=htmlspecialchars($row['service'])?></td><td><?=htmlspecialchars($row['staff'])?></td><td><?=htmlspecialchars($row['branch'])?></td><td><?=reportStatus($row['status'])?></td><td><?=reportMoney($row['verified_amount'])?></td>
   <?php elseif($section==='services'):?><td><?=htmlspecialchars($row['label'])?></td><td><?=(int)$row['bookings']?></td><td><?=(int)$row['completed']?></td><td><?=reportMoney($row['revenue'])?></td>
   <?php elseif($section==='staff'): $rate=(int)$row['assigned']?((int)$row['completed']/(int)$row['assigned'])*100:0;?><td><?=htmlspecialchars($row['label'])?></td><td><?=(int)$row['assigned']?></td><td><?=(int)$row['completed']?></td><td><?=number_format($rate,1)?>%</td><td><?=reportMoney($row['revenue'])?></td>
   <?php elseif($section==='payments'):?><td>#<?=(int)$row['id']?></td><td><?=htmlspecialchars(date('M j, Y g:i A',strtotime($row['created_at'])))?></td><td><?=htmlspecialchars($row['customer'])?></td><td><?=htmlspecialchars($row['branch'])?></td><td><?=htmlspecialchars($row['payment_method']?:'—')?></td><td><?=htmlspecialchars($row['reference_number']?:'—')?></td><td><?=reportStatus($row['status'])?></td><td><?=reportMoney($row['amount'])?></td>
   <?php elseif($section==='feedback'):?><td><?=htmlspecialchars(date('M j, Y',strtotime($row['created_at'])))?></td><td><?=htmlspecialchars($row['customer'])?></td><td><?=htmlspecialchars($row['service'])?></td><td><?=htmlspecialchars($row['branch'])?></td><td><span class="sb-rating"><?=str_repeat('★',(int)$row['rating']).str_repeat('☆',max(0,5-(int)$row['rating']))?></span></td><td><?=htmlspecialchars(ucfirst($row['status']))?></td><td class="sb-wrap"><?=htmlspecialchars($row['comment']?:'—')?></td>
   <?php else:?><td><?=htmlspecialchars($row['item_name'])?></td><td><?=htmlspecialchars($row['branch'])?></td><td><?=htmlspecialchars((string)$row['quantity'].' '.(string)$row['unit'])?></td><td><?=htmlspecialchars((string)$row['reorder_level'].' '.(string)$row['unit'])?></td><td><?=reportStatus($row['status'])?></td><?php endif;?>
  </tr><?php endforeach;?>
  <?php if(!$report[$section]):?><tr><td colspan="<?=count($tables[$section])?>" class="sb-empty">No records found for this report.</td></tr><?php endif;?>
  </tbody></table></div>
 </section>
 <?php endforeach;?>
</div>
<script>window.SB_REPORT_DATA=<?=json_encode(['daily'=>$report['daily'],'statuses'=>$report['statuses']],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;</script>
<script src="../assets/js/reports-dashboard.js"></script>
