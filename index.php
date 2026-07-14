<?php
$pageTitle = "Home";
include __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div>
    <p style="color:#A97922;font-weight:700;letter-spacing:2px;">NAILS & BODY WORX</p>
    <h1>Luxury care, smarter booking.</h1>
    <p>Experience a modern salon management and styling platform designed for effortless booking, personalized recommendations, and premium customer care.</p>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:24px;">
      <a class="btn btn-gold" href="customer/book.php">Book an Appointment</a>
      <a class="btn btn-outline" href="auth/login.php">Sign In</a>
    </div>
  </div>
  <div class="hero-card">
    <h3>Soft Opening Promotion</h3>
    <p style="font-size:54px;font-weight:800;color:#A97922;margin:12px 0;">20% OFF</p>
    <p>Enjoy exclusive opening discounts on selected services.</p>
    <button class="btn btn-gold" data-demo-alert>View Promotion</button>
  </div>
</section>

<section class="section">
  <h2>Featured Services</h2>
  <div class="grid">
    <div class="card"><h3>Nail Services</h3><p>Manicure, pedicure, nail art, gel polish, and premium nail care.</p></div>
    <div class="card"><h3>Beauty Treatments</h3><p>Personalized beauty and wellness services delivered by trained staff.</p></div>
    <div class="card"><h3>AI Styling</h3><p>Generate nail design ideas and receive intelligent style recommendations.</p></div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
