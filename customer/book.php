<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/service-catalog.php';

$pageTitle = 'Book Appointment';
$errors = [];
$success = false;

function bookingTableHasColumn(PDO $pdo, string $table, string $column): bool
{
    $statement = $pdo->prepare(
        "SELECT COUNT(*)
         FROM information_schema.columns
         WHERE table_schema = DATABASE()
           AND table_name = ?
           AND column_name = ?"
    );
    $statement->execute([$table, $column]);

    return (bool) $statement->fetchColumn();
}

function bookingCustomerLoggedIn(): bool
{
    return !empty($_SESSION['user_id'])
        && ($_SESSION['role'] ?? '') === 'customer';
}

function bookingCreateCode(): string
{
    return 'NBW-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

function bookingFetchStaff(PDO $pdo): array
{
    try {
        $statement = $pdo->query(
            "SELECT
                u.id,
                u.fullname,
                COALESCE(sp.specialty, 'Beauty Professional') AS specialty,
                u.profile_picture
             FROM users u
             LEFT JOIN staff_profiles sp ON sp.user_id = u.id
             WHERE u.role = 'staff'
               AND u.status = 'active'
             ORDER BY u.fullname ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $exception) {
        return [];
    }
}

function bookingFetchBlockedSlots(
    PDO $pdo,
    string $date,
    ?int $staffId
): array {
    if ($date === '') {
        return [];
    }

    try {
        if ($staffId) {
            $statement = $pdo->prepare(
                "SELECT TIME_FORMAT(booking_time, '%H:%i') AS booking_time
                 FROM bookings
                 WHERE booking_date = ?
                   AND staff_id = ?
                   AND status IN ('pending', 'confirmed')"
            );
            $statement->execute([$date, $staffId]);
        } else {
            $statement = $pdo->prepare(
                "SELECT TIME_FORMAT(booking_time, '%H:%i') AS booking_time
                 FROM bookings
                 WHERE booking_date = ?
                   AND status IN ('pending', 'confirmed')"
            );
            $statement->execute([$date]);
        }

        return array_values(array_filter(array_column(
            $statement->fetchAll(PDO::FETCH_ASSOC),
            'booking_time'
        )));
    } catch (Throwable $exception) {
        return [];
    }
}

$catalogServices = catalogServices();
$staffMembers = bookingFetchStaff($pdo);

$bookingDraft = $_SESSION['booking_draft'] ?? [];
$selectedServiceName = trim($_GET['service'] ?? $_POST['service_name'] ?? $bookingDraft['service_name'] ?? '');
$selectedService = $selectedServiceName !== ''
    ? catalogServiceByName($selectedServiceName)
    : null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['availability'])) {
    header('Content-Type: application/json');

    $date = trim($_GET['date'] ?? '');
    $staffId = (int) ($_GET['staff_id'] ?? 0);

    echo json_encode([
        'blocked' => bookingFetchBlockedSlots(
            $pdo,
            $date,
            $staffId > 0 ? $staffId : null
        ),
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceName = trim($_POST['service_name'] ?? '');
    $staffId = (int) ($_POST['staff_id'] ?? 0);
    $bookingDate = trim($_POST['booking_date'] ?? '');
    $bookingTime = trim($_POST['booking_time'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $designReference = trim($_POST['design_reference'] ?? '');

    $service = catalogServiceByName($serviceName);

    if (!$service) {
        $errors[] = 'Please select a valid service.';
    }

    if ($bookingDate === '') {
        $errors[] = 'Please select an appointment date.';
    } else {
        $dateObject = DateTime::createFromFormat('Y-m-d', $bookingDate);

        if (!$dateObject || $dateObject->format('Y-m-d') !== $bookingDate) {
            $errors[] = 'Please select a valid appointment date.';
        } elseif ($dateObject < new DateTime('today')) {
            $errors[] = 'The appointment date cannot be in the past.';
        }
    }

    if (!preg_match('/^\d{2}:\d{2}$/', $bookingTime)) {
        $errors[] = 'Please select an available appointment time.';
    }

    if (!$errors && !bookingCustomerLoggedIn()) {
        $_SESSION['booking_draft'] = $_POST;
        $_SESSION['login_notice'] =
            'Sign in or create a customer account to confirm your appointment.';

        header('Location: ../auth/login.php');
        exit;
    }

    if (!$errors && $service) {
        $serviceStatement = $pdo->prepare(
            "SELECT id
             FROM services
             WHERE name = ?
             LIMIT 1"
        );
        $serviceStatement->execute([$serviceName]);
        $serviceId = (int) $serviceStatement->fetchColumn();

        if ($serviceId <= 0) {
            $errors[] = 'The selected service is not available in the database.';
        }
    }

    if (!$errors && $service) {
        $conflictSql =
            "SELECT COUNT(*)
             FROM bookings
             WHERE booking_date = ?
               AND booking_time = ?
               AND status IN ('pending', 'confirmed')";

        $conflictParams = [$bookingDate, $bookingTime];

        if ($staffId > 0) {
            $conflictSql .= " AND staff_id = ?";
            $conflictParams[] = $staffId;
        }

        $conflict = $pdo->prepare($conflictSql);
        $conflict->execute($conflictParams);

        if ((int) $conflict->fetchColumn() > 0) {
            $errors[] =
                'That appointment time has just been reserved. Please choose another time.';
        }
    }

    if (!$errors && $service) {
        $columns = [
            'customer_id',
            'service_id',
            'staff_id',
            'booking_date',
            'booking_time',
            'status',
            'notes',
        ];

        $placeholders = [
            ':customer_id',
            ':service_id',
            ':staff_id',
            ':booking_date',
            ':booking_time',
            "'pending'",
            ':notes',
        ];

        $parameters = [
            'customer_id' => (int) $_SESSION['user_id'],
            'service_id' => $serviceId,
            'staff_id' => $staffId > 0 ? $staffId : null,
            'booking_date' => $bookingDate,
            'booking_time' => $bookingTime . ':00',
            'notes' => $notes,
        ];

        if (bookingTableHasColumn($pdo, 'bookings', 'booking_code')) {
            $columns[] = 'booking_code';
            $placeholders[] = ':booking_code';
            $parameters['booking_code'] = bookingCreateCode();
        }

        if (bookingTableHasColumn($pdo, 'bookings', 'quoted_price')) {
            $columns[] = 'quoted_price';
            $placeholders[] = ':quoted_price';
            $parameters['quoted_price'] = (float) $service['price'];
        }

        if (bookingTableHasColumn($pdo, 'bookings', 'estimated_duration')) {
            $columns[] = 'estimated_duration';
            $placeholders[] = ':estimated_duration';
            $parameters['estimated_duration'] = (int) $service['duration'];
        }

        if (bookingTableHasColumn($pdo, 'bookings', 'design_reference')) {
            $columns[] = 'design_reference';
            $placeholders[] = ':design_reference';
            $parameters['design_reference'] =
                $designReference !== '' ? $designReference : null;
        } elseif ($designReference !== '') {
            $parameters['notes'] .=
                ($parameters['notes'] !== '' ? "\n\n" : '')
                . 'AI design reference: '
                . $designReference;
        }

        $insert = $pdo->prepare(
            "INSERT INTO bookings ("
            . implode(', ', $columns)
            . ") VALUES ("
            . implode(', ', $placeholders)
            . ")"
        );
        $insert->execute($parameters);

        unset($_SESSION['booking_draft']);
        $_SESSION['booking_success_id'] = (int) $pdo->lastInsertId();

        header('Location: book.php?confirmed=1');
        exit;
    }
}

$confirmed = isset($_GET['confirmed'])
    && !empty($_SESSION['booking_success_id']);

if ($confirmed) {
    $confirmedBookingId = (int) $_SESSION['booking_success_id'];
    unset($_SESSION['booking_success_id']);
}

include '../includes/header.php';
?>

<main class="booking-v3-page">
    <?php if ($confirmed): ?>
        <section class="booking-confirmation">
            <div class="booking-confirmation-icon">✓</div>
            <p class="booking-kicker">Appointment request received</p>
            <h1>Your visit is now waiting for confirmation.</h1>
            <p>
                The salon will review your schedule and payment requirement.
                You can track the appointment from your customer dashboard.
            </p>

            <div class="booking-confirmation-actions">
                <a class="nbw-primary-button" href="appointments.php">
                    View my appointments
                </a>
                <a class="nbw-secondary-button" href="../index.php">
                    Return home
                </a>
            </div>
        </section>
    <?php else: ?>
        <section class="booking-v3-intro">
            <div>
                <p class="booking-kicker">Reserve your visit</p>
                <h1>Build your appointment in three simple steps.</h1>
            </div>

            <p>
                Select a real salon service, choose your preferred professional,
                then reserve an available date and time.
            </p>
        </section>

        <?php if ($errors): ?>
            <div class="booking-error-box">
                <strong>Please review your appointment:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="booking-wizard-shell">
            <div class="booking-progress" aria-label="Booking progress">
                <button class="active" type="button" data-step-jump="1">
                    <span>1</span>
                    <div><strong>Service</strong><small>Choose your treatment</small></div>
                </button>
                <i></i>
                <button type="button" data-step-jump="2">
                    <span>2</span>
                    <div><strong>Professional</strong><small>Select your staff</small></div>
                </button>
                <i></i>
                <button type="button" data-step-jump="3">
                    <span>3</span>
                    <div><strong>Schedule</strong><small>Date, time, and review</small></div>
                </button>
            </div>

            <form method="POST" id="bookingForm">
                <input
                    type="hidden"
                    name="service_name"
                    id="serviceName"
                    value="<?= htmlspecialchars($selectedServiceName, ENT_QUOTES, 'UTF-8') ?>"
                >
                <input type="hidden" name="staff_id" id="staffId" value="<?= (int)($bookingDraft['staff_id'] ?? 0) ?>">
                <input type="hidden" name="booking_time" id="bookingTime" value="<?= htmlspecialchars($bookingDraft['booking_time'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <section class="booking-step active" data-booking-step="1">
                    <div class="booking-step-heading">
                        <div>
                            <p class="booking-kicker">Step 1</p>
                            <h2>Choose a service.</h2>
                        </div>

                        <label class="booking-search">
                            <span>Search</span>
                            <input
                                id="bookingServiceSearch"
                                type="search"
                                placeholder="Search nail, spa, facial..."
                            >
                        </label>
                    </div>

                    <div class="booking-category-pills" id="bookingCategoryPills">
                        <button class="active" type="button" data-category="all">
                            All
                        </button>

                        <?php foreach (catalogCategories() as $category): ?>
                            <button
                                type="button"
                                data-category="<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>"
                            >
                                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="booking-service-grid" id="bookingServiceGrid">
                        <?php foreach ($catalogServices as $service): ?>
                            <?php
                                $selected = $selectedServiceName === $service['name'];
                            ?>
                            <button
                                type="button"
                                class="booking-service-card <?= $selected ? 'selected' : '' ?>"
                                data-service-card
                                data-category="<?= htmlspecialchars($service['category'], ENT_QUOTES, 'UTF-8') ?>"
                                data-name="<?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?>"
                                data-price="<?= htmlspecialchars((string) $service['price'], ENT_QUOTES, 'UTF-8') ?>"
                                data-duration="<?= (int) $service['duration'] ?>"
                                data-image="<?= htmlspecialchars($service['image'], ENT_QUOTES, 'UTF-8') ?>"
                                data-search="<?= htmlspecialchars(
                                    strtolower($service['name'] . ' ' . $service['description']),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >
                                <span class="booking-service-card-image">
                                    <img
                                        loading="lazy"
                                        src="<?= htmlspecialchars($service['image'], ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                    <?php if ($service['featured']): ?>
                                        <b>Popular</b>
                                    <?php endif; ?>
                                </span>

                                <span class="booking-service-card-copy">
                                    <small>
                                        <?= htmlspecialchars(
                                            ucwords(str_replace('-', ' ', $service['category'])),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </small>
                                    <strong>
                                        <?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </strong>
                                    <em>
                                        <?= catalogMoney($service['price']) ?>
                                        · ~<?= (int) $service['duration'] ?> min
                                    </em>
                                </span>

                                <span class="booking-select-circle">✓</span>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="booking-step-footer">
                        <span id="serviceSelectionMessage">
                            Select a service to continue.
                        </span>
                        <button
                            type="button"
                            class="booking-next-button"
                            data-next-step="2"
                        >
                            Choose professional
                            <b>↗</b>
                        </button>
                    </div>
                </section>

                <section class="booking-step" data-booking-step="2">
                    <div class="booking-step-heading">
                        <div>
                            <p class="booking-kicker">Step 2</p>
                            <h2>Choose your preferred professional.</h2>
                        </div>

                        <p>
                            Selecting “Any available professional” gives the salon
                            flexibility to confirm your appointment faster.
                        </p>
                    </div>

                    <div class="booking-staff-grid">
                        <button
                            type="button"
                            class="booking-staff-card selected"
                            data-staff-card
                            data-staff-id="0"
                            data-staff-name="Any Available Professional"
                        >
                            <span class="booking-any-staff">✦</span>
                            <div>
                                <small>Fastest confirmation</small>
                                <strong>Any Available Professional</strong>
                                <em>Let the salon assign the best available staff member.</em>
                            </div>
                            <span class="booking-select-circle">✓</span>
                        </button>

                        <?php foreach ($staffMembers as $staff): ?>
                            <button
                                type="button"
                                class="booking-staff-card"
                                data-staff-card
                                data-staff-id="<?= (int) $staff['id'] ?>"
                                data-staff-name="<?= htmlspecialchars($staff['fullname'], ENT_QUOTES, 'UTF-8') ?>"
                            >
                                <?php if (!empty($staff['profile_picture'])): ?>
                                    <img
                                        src="../uploads/profiles/<?= htmlspecialchars(
                                            $staff['profile_picture'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        alt="<?= htmlspecialchars($staff['fullname'], ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                <?php else: ?>
                                    <span class="booking-staff-initial">
                                        <?= htmlspecialchars(
                                            strtoupper(substr($staff['fullname'], 0, 1)),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>
                                <?php endif; ?>

                                <div>
                                    <small><?= htmlspecialchars($staff['specialty'], ENT_QUOTES, 'UTF-8') ?></small>
                                    <strong><?= htmlspecialchars($staff['fullname'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <em>Active salon professional</em>
                                </div>

                                <span class="booking-select-circle">✓</span>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="booking-step-footer">
                        <button type="button" class="booking-back-button" data-back-step="1">
                            ← Back to services
                        </button>

                        <button
                            type="button"
                            class="booking-next-button"
                            data-next-step="3"
                        >
                            Choose schedule
                            <b>↗</b>
                        </button>
                    </div>
                </section>

                <section class="booking-step" data-booking-step="3">
                    <div class="booking-schedule-layout">
                        <div class="booking-schedule-main">
                            <div class="booking-step-heading">
                                <div>
                                    <p class="booking-kicker">Step 3</p>
                                    <h2>Select your schedule.</h2>
                                </div>
                            </div>

                            <div class="booking-date-card">
                                <label for="bookingDate">Appointment date</label>
                                <input
                                    id="bookingDate"
                                    name="booking_date"
                                    type="date"
                                    min="<?= date('Y-m-d') ?>"
                                    value="<?= htmlspecialchars($bookingDraft['booking_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                >
                            </div>

                            <div class="booking-time-heading">
                                <div>
                                    <strong>Available times</strong>
                                    <span id="availabilityNote">
                                        Select a date to load available slots.
                                    </span>
                                </div>
                            </div>

                            <div class="booking-time-grid" id="bookingTimeGrid">
                                <?php
                                    $openingHour = 9;
                                    $closingHour = 19;

                                    for ($hour = $openingHour; $hour <= $closingHour; $hour++) {
                                        foreach ([0, 30] as $minute) {
                                            if ($hour === $closingHour && $minute === 30) {
                                                continue;
                                            }

                                            $value = sprintf('%02d:%02d', $hour, $minute);
                                            $label = date('g:i A', strtotime($value));
                                ?>
                                    <button
                                        type="button"
                                        data-time-slot="<?= $value ?>"
                                        disabled
                                    >
                                        <?= $label ?>
                                    </button>
                                <?php
                                        }
                                    }
                                ?>
                            </div>

                            <div class="booking-extra-grid">
                                <label>
                                    <span>Special instructions</span>
                                    <textarea
                                        name="notes"
                                        rows="5"
                                        placeholder="Allergies, preferences, requested style, or anything the salon should know."
                                    ><?= htmlspecialchars($bookingDraft['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                </label>

                                <label>
                                    <span>AI design reference <small>Optional</small></span>
                                    <input
                                        name="design_reference"
                                        type="text"
                                        placeholder="Paste a saved design name, image path, or reference code."
                                        value="<?= htmlspecialchars($bookingDraft['design_reference'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                </label>
                            </div>
                        </div>

                        <aside class="booking-summary" id="bookingSummary">
                            <p class="booking-kicker">Appointment summary</p>
                            <h2>Your visit</h2>

                            <div class="booking-summary-preview">
                                <img
                                    id="summaryImage"
                                    src="../assets/images/services/nails/nails-01.webp"
                                    alt=""
                                >
                            </div>

                            <dl>
                                <div>
                                    <dt>Service</dt>
                                    <dd id="summaryService">Not selected</dd>
                                </div>
                                <div>
                                    <dt>Professional</dt>
                                    <dd id="summaryStaff">Any Available Professional</dd>
                                </div>
                                <div>
                                    <dt>Date</dt>
                                    <dd id="summaryDate">Not selected</dd>
                                </div>
                                <div>
                                    <dt>Time</dt>
                                    <dd id="summaryTime">Not selected</dd>
                                </div>
                                <div>
                                    <dt>Duration</dt>
                                    <dd id="summaryDuration">—</dd>
                                </div>
                            </dl>

                            <div class="booking-summary-total">
                                <span>Estimated price</span>
                                <strong id="summaryPrice">₱0</strong>
                            </div>

                            <p class="booking-summary-note">
                                Final confirmation remains subject to availability
                                and the salon’s payment policy.
                            </p>

                            <button class="booking-confirm-button" type="submit">
                                <?= bookingCustomerLoggedIn()
                                    ? 'Submit appointment request'
                                    : 'Sign in and continue' ?>
                                <span>↗</span>
                            </button>
                        </aside>
                    </div>

                    <div class="booking-step-footer">
                        <button type="button" class="booking-back-button" data-back-step="2">
                            ← Back to professionals
                        </button>
                    </div>
                </section>
            </form>
        </div>
    <?php endif; ?>
</main>

<script>
window.NBW_BOOKING_INITIAL_SERVICE = <?= json_encode($selectedService, JSON_UNESCAPED_SLASHES) ?>;
window.NBW_BOOKING_DRAFT = <?= json_encode($bookingDraft, JSON_UNESCAPED_SLASHES) ?>;
</script>

<link rel="stylesheet" href="../assets/css/booking-v3.css">
<script src="../assets/js/booking-v3.js"></script>

<?php include '../includes/footer.php'; ?>
