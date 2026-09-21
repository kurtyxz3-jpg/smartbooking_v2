<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';

$pageTitle = 'Services';
$error = '';
$success = '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function adminServiceCsrf(): string
{
    if (empty($_SESSION['admin_service_csrf'])) {
        $_SESSION['admin_service_csrf'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['admin_service_csrf'];
}

function adminServiceVerifyCsrf(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['admin_service_csrf'])
        && hash_equals((string) $_SESSION['admin_service_csrf'], $token);
}

function adminServiceSlug(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value !== '' ? $value : 'service';
}

function adminUniqueServiceSlug(
    PDO $pdo,
    string $requestedSlug,
    ?int $ignoreId = null
): string {
    $base = adminServiceSlug($requestedSlug);
    $slug = $base;
    $suffix = 2;

    while (true) {
        $sql = 'SELECT COUNT(*) FROM services WHERE slug = ?';
        $params = [$slug];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        if ((int) $statement->fetchColumn() === 0) {
            return $slug;
        }

        $slug = $base . '-' . $suffix;
        $suffix++;
    }
}

function adminServiceImageUpload(
    array $file,
    ?string $existingPath = null
): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $existingPath;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The service image could not be uploaded.');
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('The service image must be 5 MB or smaller.');
    }

    $temporaryPath = (string) ($file['tmp_name'] ?? '');
    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowedTypes[$mimeType])) {
        throw new RuntimeException(
            'Only JPG, PNG, and WebP service images are allowed.'
        );
    }

    $uploadDirectory =
        dirname(__DIR__)
        . '/assets/images/services/uploads';

    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true)) {
        throw new RuntimeException(
            'The service-image upload folder could not be created.'
        );
    }

    $filename =
        'service-'
        . date('Ymd-His')
        . '-'
        . bin2hex(random_bytes(5))
        . '.'
        . $allowedTypes[$mimeType];

    $destination = $uploadDirectory . '/' . $filename;

    if (!move_uploaded_file($temporaryPath, $destination)) {
        throw new RuntimeException('The service image could not be saved.');
    }

    return 'assets/images/services/uploads/' . $filename;
}

function adminServiceFlash(string $type, string $message): never
{
    $_SESSION['admin_service_flash'] = compact('type', 'message');
    header('Location: services.php');
    exit;
}

function adminServicePhoto(string $serviceName, string $categoryName = ''): string
{
    $text = strtolower($serviceName . ' ' . $categoryName);
    $photos = [
        'eyeliner-tattoo.jpg' => ['eyeliner tattoo','winged eyeliner'],
        'tattoo-removal.jpg' => ['tattoo removal','laser brow removal'],
        'diode-laser.jpg' => ['diode','carbon laser','blackdoll','melasma laser'],
        'botox.jpg' => ['botox','jawtox','alartox','traptox','sweatox'],
        'injectables.jpg' => ['filler','hiko nose','glutathione drip'],
        'hifu.jpg' => ['hifu'],
        'body-contouring.jpg' => ['emsculpt','mesolipo','rf face','rf arms','rf legs','rf tummy','rf thigh'],
        'service-package.jpg' => ['package'],
        'ear-candling.jpg' => ['ear candl','ear candle'],
        'head-spa.jpg' => ['head spa'],
        'hand-scrub.jpg' => ['hand scrub'],
        'body-scrub.jpg' => ['body scrub','whitening full body'],
        'kids-haircut.jpg' => ['kid','child','junior'],
        'lip-blush.jpg' => ['lip','blush'],
        'waxing.jpg' => ['wax','thread'],
        'massage.jpg' => ['massage','spa','body'],
        'eyebrows.jpg' => ['brow','eyebrow'],
        'eyelashes.jpg' => ['lash','eyelash'],
        'facial.jpg' => ['facial','skin','rejuven','glow'],
        'hair-color.jpg' => ['color','dye','highlight','balayage'],
        'haircut.jpg' => ['hair','cut','style','blow'],
        'nail-art.jpg' => ['nail art','gel','polish','acrylic','extension'],
        'pedicure.jpg' => ['pedicure','foot','feet'],
        'manicure.jpg' => ['manicure','nail','hand'],
    ];
    foreach ($photos as $photo => $keywords) {
        foreach ($keywords as $keyword) if (str_contains($text, $keyword)) return $photo;
    }
    $names = array_keys($photos);
    return $names[abs(crc32($serviceName)) % count($names)];
}

/**
 * @return array<string, true>
 */
function adminServiceTableColumns(PDO $pdo, string $table): array
{
    $statement = $pdo->prepare(
        "SELECT column_name
         FROM information_schema.columns
         WHERE table_schema = DATABASE()
           AND table_name = ?"
    );
    $statement->execute([$table]);

    $columns = [];

    foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $column) {
        $columns[(string) $column] = true;
    }

    return $columns;
}

$requiredServiceColumns = [
    'id',
    'category_id',
    'slug',
    'name',
    'description',
    'price',
    'price_note',
    'duration',
    'image',
    'featured',
    'display_order',
    'status',
    'created_at',
    'updated_at',
];

$requiredCategoryColumns = [
    'id',
    'name',
    'description',
    'status',
    'display_order',
    'created_at',
    'updated_at',
];

$serviceColumns = adminServiceTableColumns($pdo, 'services');
$categoryColumns = adminServiceTableColumns($pdo, 'service_categories');
$missingServiceColumns = array_values(
    array_diff($requiredServiceColumns, array_keys($serviceColumns))
);
$missingCategoryColumns = array_values(
    array_diff($requiredCategoryColumns, array_keys($categoryColumns))
);

if (!$serviceColumns || $missingServiceColumns || $missingCategoryColumns) {
    $missingParts = [];

    if (!$serviceColumns) {
        $missingParts[] = 'the services table';
    } elseif ($missingServiceColumns) {
        $missingParts[] =
            'services.' . implode(', services.', $missingServiceColumns);
    }

    if (!$categoryColumns) {
        $missingParts[] = 'the service_categories table';
    } elseif ($missingCategoryColumns) {
        $missingParts[] =
            'service_categories.'
            . implode(', service_categories.', $missingCategoryColumns);
    }

    http_response_code(503);
    exit(
        'The Services module database migration has not been fully applied. '
        . 'Missing: '
        . htmlspecialchars(
            implode('; ', $missingParts),
            ENT_QUOTES,
            'UTF-8'
        )
        . '. Import database/063_services_schema_compatibility.sql, '
        . 'then reload this page.'
    );
}

$flash = $_SESSION['admin_service_flash'] ?? null;
unset($_SESSION['admin_service_flash']);

if (is_array($flash)) {
    if (($flash['type'] ?? '') === 'success') {
        $success = (string) ($flash['message'] ?? '');
    } else {
        $error = (string) ($flash['message'] ?? '');
    }
}

$categories = [];

if (adminTableExists($pdo, 'service_categories')) {
    $categories = $pdo->query(
        "SELECT id, name, status
         FROM service_categories
         ORDER BY display_order ASC, name ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!adminServiceVerifyCsrf($_POST['csrf_token'] ?? null)) {
        adminServiceFlash(
            'error',
            'Your session token expired. Please try again.'
        );
    }

    $action = trim((string) ($_POST['action'] ?? ''));

    try {
        if (in_array($action, ['create', 'update'], true)) {
            $serviceId =
                $action === 'update'
                    ? filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT)
                    : null;

            if ($action === 'update' && !$serviceId) {
                throw new RuntimeException('The selected service was not found.');
            }

            $name = trim((string) ($_POST['name'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            $categoryId = filter_input(
                INPUT_POST,
                'category_id',
                FILTER_VALIDATE_INT
            );
            $price = filter_input(
                INPUT_POST,
                'price',
                FILTER_VALIDATE_FLOAT
            );
            $priceNote = trim((string) ($_POST['price_note'] ?? ''));
            $duration = filter_input(
                INPUT_POST,
                'duration',
                FILTER_VALIDATE_INT
            );
            $status = trim((string) ($_POST['status'] ?? 'active'));
            $featured = isset($_POST['featured']) ? 1 : 0;
            $displayOrder = filter_input(
                INPUT_POST,
                'display_order',
                FILTER_VALIDATE_INT
            );
            $existingImage = trim((string) ($_POST['existing_image'] ?? ''));
            $manualImage = trim((string) ($_POST['image_path'] ?? ''));

            if ($name === '') {
                throw new RuntimeException('Service name is required.');
            }

            if ($price === false || $price < 0) {
                throw new RuntimeException('Enter a valid service price.');
            }

            if ($duration === false || $duration < 5 || $duration > 1440) {
                throw new RuntimeException(
                    'Duration must be between 5 and 1,440 minutes.'
                );
            }

            if (!in_array($status, ['active', 'inactive'], true)) {
                throw new RuntimeException('Choose a valid service status.');
            }

            if ($categoryId === false) {
                $categoryId = null;
            }

            if ($displayOrder === false || $displayOrder < 0) {
                $displayOrder = 0;
            }

            $requestedSlug = trim(
                (string) ($_POST['slug'] ?? $name)
            );

            $slug = adminUniqueServiceSlug(
                $pdo,
                $requestedSlug !== '' ? $requestedSlug : $name,
                $serviceId ? (int) $serviceId : null
            );

            $baseImage =
                $manualImage !== ''
                    ? ltrim($manualImage, '/')
                    : ($existingImage !== '' ? $existingImage : null);

            $imagePath = adminServiceImageUpload(
                $_FILES['service_image'] ?? [],
                $baseImage
            );

            if ($action === 'create') {
                $statement = $pdo->prepare(
                    "INSERT INTO services (
                        category_id,
                        slug,
                        name,
                        description,
                        price,
                        price_note,
                        duration,
                        image,
                        featured,
                        display_order,
                        status
                    ) VALUES (
                        :category_id,
                        :slug,
                        :name,
                        :description,
                        :price,
                        :price_note,
                        :duration,
                        :image,
                        :featured,
                        :display_order,
                        :status
                    )"
                );

                $statement->execute([
                    'category_id' => $categoryId,
                    'slug' => $slug,
                    'name' => $name,
                    'description' =>
                        $description !== '' ? $description : null,
                    'price' => $price,
                    'price_note' =>
                        $priceNote !== '' ? $priceNote : null,
                    'duration' => $duration,
                    'image' => $imagePath,
                    'featured' => $featured,
                    'display_order' => $displayOrder,
                    'status' => $status,
                ]);

                adminServiceFlash(
                    'success',
                    'The new service was added successfully.'
                );
            }

            $statement = $pdo->prepare(
                "UPDATE services
                 SET category_id = :category_id,
                     slug = :slug,
                     name = :name,
                     description = :description,
                     price = :price,
                     price_note = :price_note,
                     duration = :duration,
                     image = :image,
                     featured = :featured,
                     display_order = :display_order,
                     status = :status
                 WHERE id = :id"
            );

            $statement->execute([
                'category_id' => $categoryId,
                'slug' => $slug,
                'name' => $name,
                'description' =>
                    $description !== '' ? $description : null,
                'price' => $price,
                'price_note' =>
                    $priceNote !== '' ? $priceNote : null,
                'duration' => $duration,
                'image' => $imagePath,
                'featured' => $featured,
                'display_order' => $displayOrder,
                'status' => $status,
                'id' => $serviceId,
            ]);

            adminServiceFlash(
                'success',
                'The service was updated successfully.'
            );
        }

        $serviceId = filter_input(
            INPUT_POST,
            'service_id',
            FILTER_VALIDATE_INT
        );

        if (
            in_array(
                $action,
                ['toggle_status', 'toggle_featured', 'remove'],
                true
            )
            && !$serviceId
        ) {
            throw new RuntimeException('The selected service was not found.');
        }

        if ($action === 'toggle_status') {
            $statement = $pdo->prepare(
                "UPDATE services
                 SET status =
                    CASE
                        WHEN status = 'active' THEN 'inactive'
                        ELSE 'active'
                    END
                 WHERE id = ?"
            );
            $statement->execute([$serviceId]);

            adminServiceFlash(
                'success',
                'The service availability was updated.'
            );
        }

        if ($action === 'toggle_featured') {
            $statement = $pdo->prepare(
                "UPDATE services
                 SET featured =
                    CASE
                        WHEN featured = 1 THEN 0
                        ELSE 1
                    END
                 WHERE id = ?"
            );
            $statement->execute([$serviceId]);

            adminServiceFlash(
                'success',
                'The featured-service setting was updated.'
            );
        }

        if ($action === 'remove') {
            $bookingCount = 0;

            if (adminTableExists($pdo, 'bookings')) {
                $statement = $pdo->prepare(
                    "SELECT COUNT(*)
                     FROM bookings
                     WHERE service_id = ?"
                );
                $statement->execute([$serviceId]);
                $bookingCount = (int) $statement->fetchColumn();
            }

            if ($bookingCount > 0) {
                $statement = $pdo->prepare(
                    "UPDATE services
                     SET status = 'inactive'
                     WHERE id = ?"
                );
                $statement->execute([$serviceId]);

                adminServiceFlash(
                    'success',
                    'The service has booking history, so it was archived instead of permanently deleted.'
                );
            }

            $statement = $pdo->prepare(
                "DELETE FROM services
                 WHERE id = ?"
            );
            $statement->execute([$serviceId]);

            adminServiceFlash(
                'success',
                'The unused service was permanently removed.'
            );
        }
    } catch (Throwable $exception) {
        adminServiceFlash('error', $exception->getMessage());
    }
}

$search = trim((string) ($_GET['search'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? 'all'));
$categoryFilter = filter_input(
    INPUT_GET,
    'category',
    FILTER_VALIDATE_INT
);
$featuredFilter = trim((string) ($_GET['featured'] ?? 'all'));
$sort = trim((string) ($_GET['sort'] ?? 'newest'));
$page = max(
    1,
    (int) (filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1)
);
$perPage = 12;
$offset = ($page - 1) * $perPage;

$allowedStatuses = ['all', 'active', 'inactive'];
$allowedFeatured = ['all', 'yes', 'no'];
$allowedSorts = [
    'newest',
    'name_asc',
    'name_desc',
    'price_low',
    'price_high',
    'order',
];

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

if (!in_array($featuredFilter, $allowedFeatured, true)) {
    $featuredFilter = 'all';
}

if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'newest';
}

$where = ['1 = 1'];
$params = [];

if ($search !== '') {
    $where[] =
        "(s.name LIKE :search_name
          OR s.description LIKE :search_description
          OR s.slug LIKE :search_slug)";

    $searchValue = '%' . $search . '%';

    $params['search_name'] = $searchValue;
    $params['search_description'] = $searchValue;
    $params['search_slug'] = $searchValue;
}

if ($statusFilter !== 'all') {
    $where[] = 's.status = :status';
    $params['status'] = $statusFilter;
}

if ($categoryFilter) {
    $where[] = 's.category_id = :category_id';
    $params['category_id'] = $categoryFilter;
}

if ($featuredFilter === 'yes') {
    $where[] = 's.featured = 1';
} elseif ($featuredFilter === 'no') {
    $where[] = 's.featured = 0';
}

$orderBy = match ($sort) {
    'name_asc' => 's.name ASC',
    'name_desc' => 's.name DESC',
    'price_low' => 's.price ASC',
    'price_high' => 's.price DESC',
    'order' => 's.display_order ASC, s.name ASC',
    default => 's.id DESC',
};

$whereSql = implode(' AND ', $where);

$countStatement = $pdo->prepare(
    "SELECT COUNT(*)
     FROM services s
     WHERE {$whereSql}"
);
$countStatement->execute($params);
$totalRows = (int) $countStatement->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));

$listStatement = $pdo->prepare(
    "SELECT
        s.*,
        c.name AS category_name,
        (
            SELECT COUNT(*)
            FROM bookings b
            WHERE b.service_id = s.id
        ) AS booking_count
     FROM services s
     LEFT JOIN service_categories c ON c.id = s.category_id
     WHERE {$whereSql}
     ORDER BY {$orderBy}
     LIMIT :limit OFFSET :offset"
);

foreach ($params as $key => $value) {
    $listStatement->bindValue(
        ':' . $key,
        $value,
        is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
    );
}

$listStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
$listStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStatement->execute();
$services = $listStatement->fetchAll(PDO::FETCH_ASSOC);


$queryForPagination = $_GET;
unset($queryForPagination['page']);

include '../includes/admin-header.php';
?>

<link rel="stylesheet" href="../assets/css/admin-services.css?v=090">

<div class="admin-layout">
    <?php include '../includes/admin-sidebar.php'; ?>

    <main class="admin-content">
        <header class="admin-page-header admin-service-header">
            <div>
                <p class="admin-service-kicker">Service catalogue</p>
                <h1>Services</h1>
                <p>
                    Manage prices, duration, categories, photos, visibility,
                    and booking availability.
                </p>
            </div>
        </header>

        <?php if ($success !== ''): ?>
            <div class="admin-service-alert success">
                <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="admin-service-alert error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="admin-card admin-service-filter-card">
            <div class="admin-service-toolbar-heading">
                <div>
                    <p class="admin-service-kicker">Catalogue controls</p>
                    <h2>Find and manage services</h2>
                    <p>
                        Search, filter, update, feature, archive, or add a salon service.
                    </p>
                </div>

                <button
                    class="admin-btn admin-btn-primary admin-service-toolbar-add"
                    type="button"
                    data-service-open="create"
                >
                    <span>＋</span> Add Service
                </button>
            </div>

            <form method="GET" class="admin-service-filter-form">
                <div class="admin-field admin-service-search-field">
                    <label for="search">Search services</label>
                    <div class="admin-service-search">
                        <span>⌕</span>
                        <input
                            id="search"
                            name="search"
                            type="search"
                            autocomplete="off"
                            data-admin-live-search
                            value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Name, description, or slug"
                        >
                    </div>
                </div>

                <div class="admin-field">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option
                                value="<?= (int) $category['id'] ?>"
                                <?= $categoryFilter === (int) $category['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-field">
                    <label for="status">Availability</label>
                    <select id="status" name="status">
                        <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All statuses</option>
                        <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="admin-field">
                    <label for="featured">Featured</label>
                    <select id="featured" name="featured">
                        <option value="all" <?= $featuredFilter === 'all' ? 'selected' : '' ?>>All services</option>
                        <option value="yes" <?= $featuredFilter === 'yes' ? 'selected' : '' ?>>Featured only</option>
                        <option value="no" <?= $featuredFilter === 'no' ? 'selected' : '' ?>>Not featured</option>
                    </select>
                </div>

                <div class="admin-field">
                    <label for="sort">Sort by</label>
                    <select id="sort" name="sort">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest added</option>
                        <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name A–Z</option>
                        <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name Z–A</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Lowest price</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Highest price</option>
                        <option value="order" <?= $sort === 'order' ? 'selected' : '' ?>>Display order</option>
                    </select>
                </div>

                <div class="admin-service-filter-actions">
                    <button class="admin-btn admin-btn-primary" type="submit">
                        Apply Filters
                    </button>

                    <a class="admin-btn admin-btn-light" href="services.php">
                        Reset
                    </a>
                </div>
            </form>

            <div class="admin-service-live-search-status" id="adminServiceLiveStatus" aria-live="polite">
                Start typing to filter the visible service cards instantly.
            </div>
        </section>

        <div class="admin-service-live-empty" id="adminServiceLiveEmpty" hidden>
            <span>⌕</span>
            <strong>No visible service matches your search.</strong>
            <p>Try a different service name, category, description, or slug.</p>
        </div>

        <section class="admin-service-result-heading">
            <div>
                <strong><?= $totalRows ?></strong>
                <span>service<?= $totalRows === 1 ? '' : 's' ?> found</span>
            </div>

            <p>
                Inactive services stay in booking history but no longer appear
                as available choices.
            </p>
        </section>

        <?php if (!$services): ?>
            <section class="admin-card admin-service-empty">
                <span>✦</span>
                <h2>No matching services</h2>
                <p>
                    Try another search or add a new salon service to the catalogue.
                </p>
                <button
                    class="admin-btn admin-btn-primary"
                    type="button"
                    data-service-open="create"
                >
                    Add Service
                </button>
            </section>
        <?php else: ?>
            <section class="admin-service-grid" id="adminServiceGrid">
                <?php foreach ($services as $service): ?>
                    <?php
                    $servicePhoto = adminServicePhoto(
                        (string) ($service['name'] ?? ''),
                        (string) ($service['category_name'] ?? '')
                    );

                    $servicePayload = htmlspecialchars(
                        json_encode([
                            'id' => (int) $service['id'],
                            'category_id' =>
                                $service['category_id'] !== null
                                    ? (int) $service['category_id']
                                    : '',
                            'slug' => $service['slug'] ?? '',
                            'name' => $service['name'] ?? '',
                            'description' => $service['description'] ?? '',
                            'price' => $service['price'] ?? 0,
                            'price_note' => $service['price_note'] ?? '',
                            'duration' => $service['duration'] ?? 60,
                            'image' => $service['image'] ?? '',
                            'featured' => (int) ($service['featured'] ?? 0),
                            'display_order' =>
                                (int) ($service['display_order'] ?? 0),
                            'status' => $service['status'] ?? 'active',
                        ], JSON_UNESCAPED_SLASHES),
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>

                    <article
                        class="admin-service-card <?= $service['status'] === 'inactive' ? 'inactive' : '' ?>"
                        data-service-card
                        data-search-text="<?= htmlspecialchars(
                            strtolower(
                                ($service['name'] ?? '') . ' '
                                . ($service['description'] ?? '') . ' '
                                . ($service['slug'] ?? '') . ' '
                                . ($service['category_name'] ?? '')
                            ),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                        <div class="admin-service-image">
                            <img src="../assets/images/admin-service-library/<?= htmlspecialchars($servicePhoto, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">

                            <div class="admin-service-image-badges">
                                <span class="admin-service-status <?= htmlspecialchars($service['status'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= ucfirst($service['status']) ?>
                                </span>

                                <?php if ((int) $service['featured'] === 1): ?>
                                    <span class="admin-service-featured">Featured</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="admin-service-card-body">
                            <div class="admin-service-card-topline">
                                <span>
                                    <?= htmlspecialchars($service['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8') ?>
                                </span>

                                <small>#<?= (int) $service['id'] ?></small>
                            </div>

                            <h2><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></h2>

                            <p>
                                <?= htmlspecialchars(
                                    $service['description']
                                        ?: 'No service description has been added.',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </p>

                            <div class="admin-service-details">
                                <div>
                                    <small>Price</small>
                                    <strong>
                                        ₱<?= number_format((float) $service['price'], 2) ?>
                                    </strong>
                                    <?php if (!empty($service['price_note'])): ?>
                                        <span><?= htmlspecialchars($service['price_note'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <small>Duration</small>
                                    <strong><?= (int) $service['duration'] ?> min</strong>
                                    <span><?= (int) $service['booking_count'] ?> bookings</span>
                                </div>
                            </div>

                            <div class="admin-service-card-actions">
                                <button
                                    class="admin-service-action edit"
                                    type="button"
                                    data-service-edit="<?= $servicePayload ?>"
                                >
                                    Edit
                                </button>

                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= adminServiceCsrf() ?>">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>">

                                    <button
                                        class="admin-service-action"
                                        type="submit"
                                    >
                                        <?= $service['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>

                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= adminServiceCsrf() ?>">
                                    <input type="hidden" name="action" value="toggle_featured">
                                    <input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>">

                                    <button
                                        class="admin-service-action"
                                        type="submit"
                                    >
                                        <?= (int) $service['featured'] === 1 ? 'Unfeature' : 'Feature' ?>
                                    </button>
                                </form>

                                <form
                                    method="POST"
                                    data-service-remove-form
                                    data-booking-count="<?= (int) $service['booking_count'] ?>"
                                >
                                    <input type="hidden" name="csrf_token" value="<?= adminServiceCsrf() ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>">

                                    <button
                                        class="admin-service-action remove"
                                        type="submit"
                                    >
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>

            <?php if ($totalPages > 1): ?>
                <nav class="admin-service-pagination" aria-label="Services pagination">
                    <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                        <?php
                        $paginationQuery = array_merge(
                            $queryForPagination,
                            ['page' => $pageNumber]
                        );
                        ?>
                        <a
                            class="<?= $pageNumber === $page ? 'active' : '' ?>"
                            href="?<?= htmlspecialchars(http_build_query($paginationQuery), ENT_QUOTES, 'UTF-8') ?>"
                        >
                            <?= $pageNumber ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>

        <?php include '../includes/admin-footer.php'; ?>
    </main>
</div>

<div
    class="admin-service-modal"
    id="serviceModal"
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="serviceModalTitle"
>
    <div class="admin-service-modal-backdrop" data-service-close></div>

    <section class="admin-service-modal-dialog">
        <header>
            <div>
                <p class="admin-service-kicker" id="serviceModalKicker">
                    Catalogue management
                </p>

                <h2 id="serviceModalTitle">Add Service</h2>
                <p id="serviceModalDescription">
                    Add a service that customers can view and book.
                </p>
            </div>

            <button
                class="admin-service-modal-close"
                type="button"
                data-service-close
                aria-label="Close service form"
            >
                ×
            </button>
        </header>

        <form
            method="POST"
            enctype="multipart/form-data"
            id="serviceForm"
        >
            <input type="hidden" name="csrf_token" value="<?= adminServiceCsrf() ?>">
            <input type="hidden" name="action" id="serviceAction" value="create">
            <input type="hidden" name="service_id" id="serviceId">
            <input type="hidden" name="existing_image" id="serviceExistingImage">

            <div class="admin-service-form-grid">
                <div class="admin-field admin-service-form-span-2">
                    <label for="serviceName">Service name</label>
                    <input
                        id="serviceName"
                        name="name"
                        type="text"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="admin-field">
                    <label for="serviceCategory">Category</label>
                    <select id="serviceCategory" name="category_id">
                        <option value="">Uncategorized</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>">
                                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-field">
                    <label for="serviceSlug">URL slug</label>
                    <input
                        id="serviceSlug"
                        name="slug"
                        type="text"
                        maxlength="180"
                        placeholder="Generated from name"
                    >
                </div>

                <div class="admin-field">
                    <label for="servicePrice">Price</label>
                    <div class="admin-service-money-input">
                        <span>₱</span>
                        <input
                            id="servicePrice"
                            name="price"
                            type="number"
                            min="0"
                            step="0.01"
                            required
                        >
                    </div>
                </div>

                <div class="admin-field">
                    <label for="servicePriceNote">Price note</label>
                    <input
                        id="servicePriceNote"
                        name="price_note"
                        type="text"
                        maxlength="100"
                        placeholder="e.g. per nail or ₱500–₱800"
                    >
                </div>

                <div class="admin-field">
                    <label for="serviceDuration">Duration (minutes)</label>
                    <input
                        id="serviceDuration"
                        name="duration"
                        type="number"
                        min="5"
                        max="1440"
                        step="5"
                        value="60"
                        required
                    >
                </div>

                <div class="admin-field">
                    <label for="serviceDisplayOrder">Display order</label>
                    <input
                        id="serviceDisplayOrder"
                        name="display_order"
                        type="number"
                        min="0"
                        value="0"
                    >
                </div>

                <div class="admin-field">
                    <label for="serviceStatus">Availability</label>
                    <select id="serviceStatus" name="status">
                        <option value="active">Active — available online</option>
                        <option value="inactive">Inactive — hidden from booking</option>
                    </select>
                </div>

                <div class="admin-field admin-service-check-field">
                    <label>
                        <input
                            id="serviceFeatured"
                            name="featured"
                            type="checkbox"
                            value="1"
                        >
                        <span>
                            <strong>Featured service</strong>
                            <small>Highlight this service on public pages.</small>
                        </span>
                    </label>
                </div>

                <div class="admin-field admin-service-form-span-2">
                    <label for="serviceDescription">Description</label>
                    <textarea
                        id="serviceDescription"
                        name="description"
                        rows="4"
                        maxlength="1000"
                        placeholder="Describe what is included in this service."
                    ></textarea>
                </div>

                <div class="admin-field admin-service-form-span-2">
                    <label>Service image</label>
                    <div class="admin-service-image-upload" data-service-image-upload>
                        <input
                            class="admin-service-image-file"
                            id="serviceImage"
                            name="service_image"
                            type="file"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        >

                        <button
                            class="admin-service-image-drop"
                            type="button"
                            data-service-image-drop
                        >
                            <img
                                id="serviceImagePreview"
                                src="../assets/images/services/temporary-placeholders/brow-reconstruction.webp"
                                alt="Service image preview"
                            >
                            <span>
                                <strong>Drag and drop an image here</strong>
                                <small>or choose a JPG, PNG, or WebP file up to 5 MB</small>
                            </span>
                        </button>

                        <div class="admin-service-image-actions">
                            <button
                                class="admin-btn admin-btn-light"
                                type="button"
                                data-service-image-choose
                            >
                                Choose image
                            </button>
                            <button
                                class="admin-btn admin-btn-light"
                                type="button"
                                data-service-image-clear
                                hidden
                            >
                                Remove image
                            </button>
                        </div>
                    </div>
                </div>

                <div class="admin-field admin-service-form-span-2">
                    <label for="serviceImagePath">Or existing image path</label>
                    <input
                        id="serviceImagePath"
                        name="image_path"
                        type="text"
                        placeholder="assets/images/services/..."
                    >
                    <small class="admin-service-form-note">
                        Leave blank to keep the current image.
                    </small>
                </div>

            </div>

            <footer class="admin-service-modal-actions">
                <button
                    class="admin-btn admin-btn-light"
                    type="button"
                    data-service-close
                >
                    Cancel
                </button>

                <button
                    class="admin-btn admin-btn-primary"
                    type="submit"
                    id="serviceSubmitButton"
                >
                    Add Service
                </button>
            </footer>
        </form>
    </section>
</div>

<script src="../assets/js/admin-services.js?v=090"></script>
