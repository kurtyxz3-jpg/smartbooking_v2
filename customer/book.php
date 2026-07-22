<?php
$pageTitle = 'Book Appointment';
include '../includes/header.php';
?>

<main class="v2-inner-page">
    <section class="v2-book-layout">
        <div class="v2-book-intro">
            <p class="v2-kicker">Reserve your visit</p>
            <h1>A simpler way to plan your salon appointment.</h1>
            <p>
                Choose your service, preferred staff member, date, and time.
                Availability checking and payment confirmation will be connected
                to this experience as the booking module is completed.
            </p>

            <div class="v2-book-points">
                <div><span>✓</span><p>Automatic availability checking</p></div>
                <div><span>✓</span><p>Preferred staff selection</p></div>
                <div><span>✓</span><p>Appointment reminders</p></div>
                <div><span>✓</span><p>AI design attachment</p></div>
            </div>
        </div>

        <section class="v2-book-card">
            <div class="v2-book-card-heading">
                <span>Step 1 of 3</span>
                <h2>Appointment details</h2>
                <p>Tell us what you would like to book.</p>
            </div>

            <form>
                <div class="v2-form-grid">
                    <div class="form-group v2-span-2">
                        <label for="service">Service</label>
                        <select id="service">
                            <option>Gel Manicure</option>
                            <option>Classic Pedicure</option>
                            <option>Custom Nail Art</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="staff">Preferred Staff</label>
                        <select id="staff">
                            <option>Any Available Staff</option>
                            <option>Staff Member 1</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date">Date</label>
                        <input id="date" type="date">
                    </div>

                    <div class="form-group">
                        <label for="time">Time</label>
                        <input id="time" type="time">
                    </div>

                    <div class="form-group v2-span-2">
                        <label for="notes">Style preferences or instructions</label>
                        <textarea
                            id="notes"
                            rows="5"
                            placeholder="Describe the look, color, or special request you have in mind."
                        ></textarea>
                    </div>
                </div>

                <button type="button" class="v2-btn v2-btn-primary" data-demo-alert>
                    Continue to payment
                    <span>↗</span>
                </button>
            </form>
        </section>
    </section>
</main>

<?php include '../includes/footer.php'; ?>
