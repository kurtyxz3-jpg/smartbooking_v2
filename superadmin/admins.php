<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/branch-context.php';

$pageTitle = 'Administrator Management';
$message = '';
$messageType = 'success';

if (empty($_SESSION['administrator_management_csrf'])) {
    $_SESSION['administrator_management_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = (string) $_SESSION['administrator_management_csrf'];

function adminManagementEscape(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function adminManagementRedirect(string $message, string $type = 'success'): never
{
    $_SESSION['administrator_management_flash'] = ['message' => $message, 'type' => $type];
    header('Location: admins.php');
    exit;
}

if (!empty($_SESSION['administrator_management_flash'])) {
    $message = (string) ($_SESSION['administrator_management_flash']['message'] ?? '');
    $messageType = (string) ($_SESSION['administrator_management_flash']['type'] ?? 'success');
    unset($_SESSION['administrator_management_flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals($csrfToken, $submittedToken)) {
        adminManagementRedirect('Your session token expired. Please try again.', 'danger');
    }

    $action = (string) ($_POST['action'] ?? '');
    $adminId = filter_input(INPUT_POST, 'admin_id', FILTER_VALIDATE_INT);

    if ($adminId && $action === 'save_admin') {
        $fullname = trim((string) ($_POST['fullname'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phoneNumber = trim((string) ($_POST['phone_number'] ?? ''));
        $branchId = salonBranchId($pdo);
        $status = (string) ($_POST['status'] ?? 'active');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($fullname === '' || $username === '' || $email === '' || !$branchId) {
            adminManagementRedirect('Please complete all required administrator fields.', 'danger');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            adminManagementRedirect('Please enter a valid email address.', 'danger');
        }
        if (!in_array($status, ['active', 'inactive'], true)) {
            adminManagementRedirect('The selected account status is invalid.', 'danger');
        }
        if ($newPassword !== '' && strlen($newPassword) < 6) {
            adminManagementRedirect('A new password must contain at least 6 characters.', 'danger');
        }
        if ($newPassword !== $confirmPassword) {
            adminManagementRedirect('The new passwords do not match.', 'danger');
        }

        $duplicate = $pdo->prepare(
            "SELECT id FROM users
             WHERE (username = :username OR email = :email)
               AND id <> :id
             LIMIT 1"
        );
        $duplicate->execute(['username' => $username, 'email' => $email, 'id' => $adminId]);
        if ($duplicate->fetch()) {
            adminManagementRedirect('That username or email address is already in use.', 'danger');
        }

        $fields = [
            'fullname = :fullname', 'username = :username', 'email = :email',
            'phone_number = :phone_number', 'branch_id = :branch_id', 'status = :status',
        ];
        $parameters = [
            'fullname' => $fullname, 'username' => $username, 'email' => $email,
            'phone_number' => $phoneNumber !== '' ? $phoneNumber : null,
            'branch_id' => $branchId, 'status' => $status, 'id' => $adminId,
        ];
        if ($newPassword !== '') {
            $fields[] = 'password = :password';
            $parameters['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $update = $pdo->prepare(
            'UPDATE users SET ' . implode(', ', $fields) . " WHERE id = :id AND role = 'admin'"
        );
        $update->execute($parameters);
        adminManagementRedirect('Administrator information updated successfully.');
    }

    if ($adminId && $action === 'toggle_status') {
        $statement = $pdo->prepare(
            "UPDATE users
             SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END
             WHERE id = ? AND role = 'admin'"
        );
        $statement->execute([$adminId]);
        adminManagementRedirect('Administrator account status updated successfully.');
    }

    if ($adminId && $action === 'delete') {
        try {
            $statement = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
            $statement->execute([$adminId]);
            if ($statement->rowCount() === 0) {
                adminManagementRedirect('Administrator account was not found.', 'danger');
            }
            adminManagementRedirect('Administrator account deleted successfully.');
        } catch (PDOException $exception) {
            // Preserve related branch, audit, or operational history instead of forcing deletion.
            $statement = $pdo->prepare(
                "UPDATE users SET status = 'inactive' WHERE id = ? AND role = 'admin'"
            );
            $statement->execute([$adminId]);
            adminManagementRedirect(
                'This administrator has related system records, so the account was safely deactivated instead.',
                'warning'
            );
        }
    }
}

$search = trim((string) ($_GET['search'] ?? ''));
$status = (string) ($_GET['status'] ?? '');

$createdAtCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'created_at'");
$hasCreatedAt = (bool) $createdAtCheck->fetch();
$createdAtSelect = $hasCreatedAt ? 'u.created_at' : 'NULL AS created_at';

$sql = "SELECT u.id, u.fullname, u.username, u.email, u.phone_number, u.status,
               u.branch_id, {$createdAtSelect}, b.branch_name
        FROM users u
        LEFT JOIN branches b ON b.id = u.branch_id
        WHERE u.role = 'admin'";
$params = [];

if ($search !== '') {
    $sql .= " AND (u.fullname LIKE :search_name OR u.username LIKE :search_username
                   OR u.email LIKE :search_email OR u.phone_number LIKE :search_phone)";
    $term = '%' . $search . '%';
    $params = [
        'search_name' => $term, 'search_username' => $term,
        'search_email' => $term, 'search_phone' => $term,
    ];
}
if (in_array($status, ['active', 'inactive'], true)) {
    $sql .= ' AND u.status = :status';
    $params['status'] = $status;
}
$sql .= $hasCreatedAt ? ' ORDER BY u.created_at DESC' : ' ORDER BY u.id DESC';

$statement = $pdo->prepare($sql);
$statement->execute($params);
$administrators = $statement->fetchAll();

include '../includes/superadmin-header.php';
?>

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>
    <main class="content">
        <div class="page-header admins-page-header">
            <div>
                <h1 class="gold-title">Administrator Accounts</h1>
                <p>Create, update, assign, and secure administrator access.</p>
            </div>
            <a class="btn btn-gold" href="add-admin.php">Add Administrator</a>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?= adminManagementEscape($messageType) ?>">
                <?= adminManagementEscape($message) ?>
            </div>
        <?php endif; ?>

        <section class="card admin-filter-card">
            <div class="admin-section-title">
                <h2>Search &amp; Filter</h2>
                <p>Find administrators by name, username, email, phone, or account status.</p>
            </div>
            <form class="admin-filter-form" method="GET">
                <div class="filter-field admin-search-field">
                    <label for="search">Search</label>
                    <input id="search" name="search" value="<?= adminManagementEscape($search) ?>"
                           placeholder="Name, username, email, or phone">
                </div>
                
                <div class="filter-field">
                    <label for="status">Account Status</label>
                    <select id="status" name="status">
                        <option value="">All statuses</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="admin-filter-actions">
                    <button class="btn btn-gold" type="submit">Filter</button>
                    <a class="btn btn-light" href="admins.php">Reset</a>
                </div>
            </form>
        </section>

        <section class="card admin-table-card">
            <div class="admin-table-heading">
                <h2>Registered Administrators</h2>
                <p><?= count($administrators) ?> administrator record(s) found.</p>
            </div>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Administrator</th><th>Contact</th><th>Role</th><th>Account</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if (!$administrators): ?>
                        <tr><td colspan="5" class="empty-state">No administrator accounts found.</td></tr>
                    <?php else: foreach ($administrators as $administrator): ?>
                        <tr>
                            <td><div class="admin-name-cell"><strong><?= adminManagementEscape($administrator['fullname']) ?></strong><span>@<?= adminManagementEscape($administrator['username']) ?></span></div></td>
                            <td><div class="admin-contact-cell"><span><?= adminManagementEscape($administrator['email'] ?: '—') ?></span><small><?= adminManagementEscape($administrator['phone_number'] ?: 'No phone number') ?></small></div></td>
                            
                            <td><span class="admin-role-badge">Administrator</span></td>
                            <td><span class="badge badge-<?= $administrator['status'] === 'active' ? 'success' : 'inactive' ?>"><?= ucfirst(adminManagementEscape($administrator['status'])) ?></span></td>
                            <td>
                                <div class="admin-action-group">
                                    <button class="admin-action-btn admin-action-edit js-edit-admin" type="button"
                                        data-admin='<?= adminManagementEscape(json_encode([
                                            'id' => (int) $administrator['id'],
                                            'fullname' => $administrator['fullname'],
                                            'username' => $administrator['username'],
                                            'email' => $administrator['email'],
                                            'phone_number' => $administrator['phone_number'],
                                            'status' => $administrator['status'],
                                        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>'>Edit</button>
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= adminManagementEscape($csrfToken) ?>">
                                        <input type="hidden" name="admin_id" value="<?= (int) $administrator['id'] ?>">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <button class="admin-action-btn admin-action-status" type="submit"><?= $administrator['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Delete this administrator? Accounts with related records will be deactivated instead.');">
                                        <input type="hidden" name="csrf_token" value="<?= adminManagementEscape($csrfToken) ?>">
                                        <input type="hidden" name="admin_id" value="<?= (int) $administrator['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button class="admin-action-btn admin-action-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="admin-modal" id="editAdminModal" hidden aria-hidden="true">
            <button class="admin-modal-backdrop js-close-admin-modal" type="button" aria-label="Close edit administrator dialog"></button>
            <section class="admin-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="editAdminTitle">
                <div class="admin-modal-header">
                    <div><span>Account management</span><h2 id="editAdminTitle">Edit Administrator</h2></div>
                    <button class="admin-modal-close js-close-admin-modal" type="button" aria-label="Close">&times;</button>
                </div>
                <form method="POST" id="editAdminForm">
                    <input type="hidden" name="csrf_token" value="<?= adminManagementEscape($csrfToken) ?>">
                    <input type="hidden" name="action" value="save_admin">
                    <input type="hidden" name="admin_id" id="edit_admin_id">
                    <div class="admin-modal-body">
                        <div class="admin-form-section">
                            <h3>Personal &amp; Contact Information</h3>
                            <div class="admin-form-grid">
                                <div class="form-group admin-span-2"><label for="edit_fullname">Full Name *</label><input id="edit_fullname" name="fullname" required></div>
                                <div class="form-group"><label for="edit_username">Username *</label><input id="edit_username" name="username" required></div>
                                <div class="form-group"><label for="edit_email">Email Address *</label><input id="edit_email" name="email" type="email" required></div>
                                <div class="form-group admin-span-2"><label for="edit_phone_number">Phone Number</label><input id="edit_phone_number" name="phone_number" inputmode="tel"></div>
                            </div>
                        </div>
                        <div class="admin-form-section">
                            <h3>Account Access</h3>
                            <div class="admin-form-grid">
                                
                                <div class="form-group"><label for="edit_status">Account Status *</label><select id="edit_status" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                            </div>
                        </div>
                        <div class="admin-form-section">
                            <h3>Optional Password Reset</h3>
                            <p>Leave both fields blank to keep the current password.</p>
                            <div class="admin-form-grid">
                                <div class="form-group"><label for="edit_new_password">New Password</label><input id="edit_new_password" name="new_password" type="password" minlength="6" autocomplete="new-password"></div>
                                <div class="form-group"><label for="edit_confirm_password">Confirm Password</label><input id="edit_confirm_password" name="confirm_password" type="password" minlength="6" autocomplete="new-password"></div>
                            </div>
                        </div>
                    </div>
                    <div class="admin-modal-actions"><button class="btn btn-light js-close-admin-modal" type="button">Cancel</button><button class="btn btn-gold" type="submit">Save Changes</button></div>
                </form>
            </section>
        </div>

        <script src="../assets/js/administrator-management.js"></script>
        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
