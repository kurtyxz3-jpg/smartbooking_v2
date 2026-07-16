<?php
$pageTitle = 'Home';
include __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div>
    <p class="hero-eyebrow">Nails &amp; Body Worx</p>
    <h1>Luxury care, smarter booking.</h1>
    <p class="hero-copy">Experience a modern salon management and styling platform designed for effortless booking, personalized recommendations, and premium customer care.</p>
    <div class="hero-actions">
      <a class="btn btn-gold" href="customer/book.php">Book an Appointment</a>
      <a class="btn btn-light" href="auth/login.php">Sign In</a>
    </div>
  </div>
  <article class="promo-card">
    <p class="promo-label">Soft Opening Promotion</p>
    <div class="promo-value">20% OFF</div>
    <p>Enjoy exclusive opening discounts on selected services.</p>
    <a class="btn btn-gold" href="#featured-services">View Promotion</a>
  </article>
</section>

<section class="public-section" id="featured-services">
  <div class="public-section-heading">
    <h2>Featured Services</h2>
    <p>Premium care, modern styling, and personalized salon experiences.</p>
  </div>
  <div class="service-grid">
    <article class="service-card"><h3>Nail Services</h3><p>Manicure, pedicure, nail art, gel polish, and premium nail care.</p></article>
    <article class="service-card"><h3>Beauty Treatments</h3><p>Personalized beauty and wellness services delivered by trained staff.</p></article>
    <article class="service-card"><h3>AI Styling</h3><p>Generate nail design ideas and receive intelligent style recommendations.</p></article>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
