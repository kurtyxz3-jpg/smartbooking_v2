<?php
$pageTitle = 'Home';
include __DIR__ . '/includes/header.php';
?>

<main>
    <section class="v2-hero" id="home">
        <div class="v2-orb v2-orb-one"></div>
        <div class="v2-orb v2-orb-two"></div>

        <div class="v2-hero-copy reveal">
            <div class="v2-eyebrow">
                <span></span>
                AI-powered beauty and wellness
            </div>

            <h1>
                Your next salon look,
                <em>designed around you.</em>
            </h1>

            <p>
                Explore services, create AI-assisted nail ideas, choose your preferred
                staff member, and reserve your appointment in one elegant experience.
            </p>

            <div class="v2-hero-actions">
                <a class="v2-btn v2-btn-primary" href="customer/book.php">
                    Book your visit
                    <span aria-hidden="true">↗</span>
                </a>

                <a class="v2-btn v2-btn-ghost" href="#services">
                    Explore services
                </a>
            </div>

            <div class="v2-hero-proof">
                <div class="v2-avatar-stack" aria-hidden="true">
                    <span>MG</span>
                    <span>AC</span>
                    <span>JR</span>
                </div>

                <div>
                    <strong>Designed for effortless self-care</strong>
                    <small>Booking, styling inspiration, reminders, and follow-up care.</small>
                </div>
            </div>
        </div>

        <div class="v2-hero-visual reveal" data-delay="120">
            <div class="v2-visual-shell" data-tilt>
                <div class="v2-visual-topbar">
                    <span>AI Nail Studio</span>
                    <b>Live preview</b>
                </div>

                <div class="v2-nail-stage">
                    <div class="v2-hand-glow"></div>

                    <div class="v2-nail-set" aria-label="Decorative nail design preview">
                        <span class="nail nail-one"></span>
                        <span class="nail nail-two"></span>
                        <span class="nail nail-three"></span>
                        <span class="nail nail-four"></span>
                        <span class="nail nail-five"></span>
                    </div>
                </div>

                <div class="v2-design-meta">
                    <div>
                        <small>Selected style</small>
                        <strong>Champagne Chrome</strong>
                    </div>

                    <span class="v2-chip">Elegant</span>
                </div>
            </div>

            <article class="v2-floating-card v2-floating-promo">
                <span>Soft opening</span>
                <strong>20% OFF</strong>
                <small>Selected services</small>
            </article>

            <article class="v2-floating-card v2-floating-booking">
                <div class="v2-mini-icon">✓</div>
                <div>
                    <small>Next available</small>
                    <strong>Today · 3:30 PM</strong>
                </div>
            </article>
        </div>
    </section>

    <section class="v2-marquee" aria-label="Salon features">
        <div>
            <span>AI nail inspiration</span>
            <i>✦</i>
            <span>Real-time availability</span>
            <i>✦</i>
            <span>Preferred staff selection</span>
            <i>✦</i>
            <span>Booking reminders</span>
            <i>✦</i>
            <span>Secure payment review</span>
            <i>✦</i>
        </div>
    </section>

    <section class="v2-section" id="services">
        <div class="v2-section-heading reveal">
            <div>
                <p class="v2-kicker">Signature care</p>
                <h2>Services made to feel personal.</h2>
            </div>

            <p>
                Browse premium nail, beauty, and wellness experiences with clear
                pricing, duration, and style inspiration before booking.
            </p>
        </div>

        <div class="v2-service-grid">
            <article class="v2-service-card reveal">
                <div class="v2-service-art art-nails">
                    <span class="art-ring"></span>
                    <span class="art-drop"></span>
                    <span class="art-spark">✦</span>
                </div>

                <div class="v2-service-copy">
                    <span>01</span>
                    <h3>Nail artistry</h3>
                    <p>Gel polish, extensions, French tips, chrome, and custom nail art.</p>
                    <a href="customer/services.php">Discover nail services →</a>
                </div>
            </article>

            <article class="v2-service-card reveal" data-delay="80">
                <div class="v2-service-art art-wellness">
                    <span class="art-leaf leaf-one"></span>
                    <span class="art-leaf leaf-two"></span>
                    <span class="art-sun"></span>
                </div>

                <div class="v2-service-copy">
                    <span>02</span>
                    <h3>Body and wellness</h3>
                    <p>Relaxing treatments created to refresh, restore, and help you unwind.</p>
                    <a href="customer/services.php">Explore wellness care →</a>
                </div>
            </article>

            <article class="v2-service-card reveal" data-delay="160">
                <div class="v2-service-art art-ai">
                    <span class="ai-grid"></span>
                    <span class="ai-star star-one">✦</span>
                    <span class="ai-star star-two">✦</span>
                    <span class="ai-core">AI</span>
                </div>

                <div class="v2-service-copy">
                    <span>03</span>
                    <h3>AI Nail Studio</h3>
                    <p>Create three design ideas based on your color, shape, and preferred style.</p>
                    <a href="customer/ai-generator.php">Preview AI styling →</a>
                </div>
            </article>
        </div>
    </section>

    <section class="v2-ai-section">
        <div class="v2-ai-copy reveal">
            <p class="v2-kicker">Your ideas, visualized</p>
            <h2>From inspiration to appointment in one flow.</h2>

            <p>
                Describe the look you have in mind. SmartBooking prepares three visual
                directions that can be saved and attached to your appointment as a staff
                reference.
            </p>

            <div class="v2-feature-list">
                <div><span>01</span><p>Choose color, nail shape, style, and add-ons.</p></div>
                <div><span>02</span><p>Generate three personalized design samples.</p></div>
                <div><span>03</span><p>Save your favorite and book it with your preferred staff.</p></div>
            </div>

            <a class="v2-btn v2-btn-primary" href="customer/ai-generator.php">
                Open AI Nail Studio
                <span aria-hidden="true">↗</span>
            </a>
        </div>

        <div class="v2-ai-gallery reveal" data-delay="100">
            <article class="v2-ai-sample sample-one">
                <div class="sample-nail"></div>
                <span>Minimal pearl</span>
            </article>

            <article class="v2-ai-sample sample-two">
                <div class="sample-nail"></div>
                <span>Rose chrome</span>
            </article>

            <article class="v2-ai-sample sample-three">
                <div class="sample-nail"></div>
                <span>Golden French</span>
            </article>
        </div>
    </section>

    <section class="v2-section v2-process-section" id="experience">
        <div class="v2-section-heading reveal">
            <div>
                <p class="v2-kicker">Simple by design</p>
                <h2>Your appointment, without the back-and-forth.</h2>
            </div>

            <p>
                The booking journey is designed to help customers make confident choices
                while preventing schedule conflicts for the salon.
            </p>
        </div>

        <div class="v2-process-grid">
            <article class="v2-process-card reveal">
                <span>1</span>
                <h3>Explore</h3>
                <p>Compare services, prices, estimated duration, promotions, and style previews.</p>
            </article>

            <article class="v2-process-card reveal" data-delay="70">
                <span>2</span>
                <h3>Personalize</h3>
                <p>Select your schedule, preferred staff, and any special styling instructions.</p>
            </article>

            <article class="v2-process-card reveal" data-delay="140">
                <span>3</span>
                <h3>Confirm</h3>
                <p>Submit your down payment, receive confirmation, and get appointment reminders.</p>
            </article>

            <article class="v2-process-card reveal" data-delay="210">
                <span>4</span>
                <h3>Return</h3>
                <p>Share feedback, rebook quickly, and discover offers based on your experience.</p>
            </article>
        </div>
    </section>

    <section class="v2-story-section">
        <div class="v2-story-panel reveal">
            <p class="v2-kicker">A calmer salon experience</p>
            <blockquote>
                “Beauty appointments should feel exciting before you even arrive.”
            </blockquote>

            <p>
                SmartBooking connects inspiration, scheduling, payment confirmation,
                reminders, and post-service care in one customer-friendly platform.
            </p>
        </div>

        <div class="v2-story-stats reveal" data-delay="100">
            <div><strong>3</strong><span>AI design samples per generation</span></div>
            <div><strong>50%</strong><span>Down payment for slot confirmation</span></div>
            <div><strong>30m</strong><span>Appointment reminder before schedule</span></div>
            <div><strong>15m</strong><span>Grace period for confirmed appointments</span></div>
        </div>
    </section>

    <section class="v2-section v2-faq-section" id="faq">
        <div class="v2-section-heading reveal">
            <div>
                <p class="v2-kicker">Questions, answered</p>
                <h2>Plan your visit with confidence.</h2>
            </div>
        </div>

        <div class="v2-faq-list reveal">
            <article class="v2-faq-item">
                <button type="button" aria-expanded="false">
                    How do I confirm my appointment?
                    <span>+</span>
                </button>

                <div class="v2-faq-answer">
                    <p>
                        Choose your service, staff, date, and time, then submit the required
                        down payment. The booking is confirmed after payment verification.
                    </p>
                </div>
            </article>

            <article class="v2-faq-item">
                <button type="button" aria-expanded="false">
                    Can I choose a specific staff member?
                    <span>+</span>
                </button>

                <div class="v2-faq-answer">
                    <p>
                        Yes. Available and unavailable staff members can be displayed based
                        on the service, date, and time you choose.
                    </p>
                </div>
            </article>

            <article class="v2-faq-item">
                <button type="button" aria-expanded="false">
                    What does the AI Nail Studio create?
                    <span>+</span>
                </button>

                <div class="v2-faq-answer">
                    <p>
                        It creates three nail art concepts based on your preferred color,
                        design style, add-ons, and written instructions.
                    </p>
                </div>
            </article>

            <article class="v2-faq-item">
                <button type="button" aria-expanded="false">
                    Will I receive appointment reminders?
                    <span>+</span>
                </button>

                <div class="v2-faq-answer">
                    <p>
                        The proposed system supports SMS and push reminders, including a
                        reminder 30 minutes before the confirmed appointment.
                    </p>
                </div>
            </article>
        </div>
    </section>

    <section class="v2-final-cta reveal">
        <div>
            <p class="v2-kicker">Ready when you are</p>
            <h2>Make your next appointment feel effortless.</h2>
        </div>

        <div>
            <a class="v2-btn v2-btn-light" href="auth/login.php">Sign in</a>
            <a class="v2-btn v2-btn-primary" href="customer/book.php">
                Book now
                <span aria-hidden="true">↗</span>
            </a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
