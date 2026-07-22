<?php
$pageTitle = 'Services';
include '../includes/header.php';
?>

<main class="v2-inner-page">
    <section class="v2-inner-hero">
        <p class="v2-kicker">Our menu</p>
        <h1>Choose care that feels made for you.</h1>
        <p>
            Clear pricing, service details, and style inspiration help you book
            with confidence.
        </p>
    </section>

    <section class="v2-inner-section">
        <div class="v2-menu-grid">
            <article class="v2-menu-card">
                <span>Signature nails</span>
                <h2>Gel Manicure</h2>
                <p>Premium preparation, gel color, and a lasting glossy finish.</p>
                <div><strong>₱650</strong><small>Estimated 60 minutes</small></div>
                <a class="v2-btn v2-btn-primary" href="book.php">Book service</a>
            </article>

            <article class="v2-menu-card">
                <span>Classic care</span>
                <h2>Classic Pedicure</h2>
                <p>Relaxing foot care, shaping, cuticle care, and polish.</p>
                <div><strong>₱550</strong><small>Estimated 60 minutes</small></div>
                <a class="v2-btn v2-btn-primary" href="book.php">Book service</a>
            </article>

            <article class="v2-menu-card v2-menu-card-featured">
                <span>Personalized design</span>
                <h2>Custom Nail Art</h2>
                <p>Bring a reference or create an AI-assisted concept before booking.</p>
                <div><strong>From ₱800</strong><small>Duration depends on design</small></div>
                <a class="v2-btn v2-btn-light" href="ai-generator.php">Create a design</a>
            </article>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>
