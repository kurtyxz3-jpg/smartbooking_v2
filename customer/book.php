<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Book Appointment | NBW</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body><?php $pageTitle="Book Appointment"; include "../includes/header.php"; ?>
<section class="section"><div class="card" style="max-width:760px;margin:auto;">
<h2 style="text-align:left;">Book an Appointment</h2>
<form>
<div class="form-group"><label>Service</label><select><option>Gel Manicure</option><option>Classic Pedicure</option><option>Nail Art</option></select></div>
<div class="form-group"><label>Preferred Staff</label><select><option>Any Available Staff</option><option>Staff Member 1</option></select></div>
<div class="form-group"><label>Date</label><input type="date"></div>
<div class="form-group"><label>Time</label><input type="time"></div>
<div class="form-group"><label>Notes</label><textarea rows="4" placeholder="Add preferences or instructions"></textarea></div>
<button type="button" class="btn btn-gold" data-demo-alert>Continue to Payment</button>
</form></div></section><?php include "../includes/footer.php"; ?></body></html>