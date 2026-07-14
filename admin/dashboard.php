<?php
$role = "Administrator";
$title = "Admin Dashboard";
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= $title ?> | NBW</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="dashboard">
<aside class="sidebar">
<h2>NBW</h2><p><?= $role ?> Portal</p>
<a class="active" href="#">Dashboard</a>
<a href="#" data-demo-alert>Appointments</a>
<a href="#" data-demo-alert>Services</a>
<a href="#" data-demo-alert>Payments</a>
<a href="#" data-demo-alert>Messages</a>
<a href="../auth/login.php">Logout</a>
</aside>
<main class="content">
<h1><?= $title ?></h1><p>Welcome to the Version 2 UI prototype.</p>
<div class="kpis">
<div class="kpi"><small>Today's Appointments</small><h2>8</h2></div>
<div class="kpi"><small>Pending Requests</small><h2>3</h2></div>
<div class="kpi"><small>Completed Services</small><h2>21</h2></div>
<div class="kpi"><small>Revenue</small><h2>₱18,450</h2></div>
</div>
<div class="table-wrap"><table><thead><tr><th>Customer</th><th>Service</th><th>Time</th><th>Status</th></tr></thead>
<tbody>
<tr><td>Maria Santos</td><td>Gel Manicure</td><td>10:00 AM</td><td><span class="badge badge-success">Confirmed</span></td></tr>
<tr><td>Ana Cruz</td><td>Pedicure</td><td>11:30 AM</td><td><span class="badge badge-pending">Pending</span></td></tr>
<tr><td>Joy Reyes</td><td>Nail Art</td><td>1:00 PM</td><td><span class="badge badge-success">Paid</span></td></tr>
</tbody></table></div>
</main></div><script src="../assets/js/main.js"></script></body></html>