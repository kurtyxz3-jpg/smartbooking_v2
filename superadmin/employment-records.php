<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';

$pageTitle = 'Employment Records';
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recordId = filter_input(INPUT_POST, 'record_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';

    if ($recordId && $action === 'archive') {
        $statement = $pdo->prepare(
            "UPDATE employment_records
             SET record_status = 'archived'
             WHERE id = ?"
        );
        $statement->execute([$recordId]);

        $message = 'Employment record archived successfully.';
    }

    if ($recordId && $action === 'restore') {
        $statement = $pdo->prepare(
            "UPDATE employment_records
             SET record_status = 'active'
             WHERE id = ?"
        );
        $statement->execute([$recordId]);

        $message = 'Employment record restored successfully.';
    }

    if ($recordId && $action === 'delete') {
        $statement = $pdo->prepare(
            "DELETE FROM employment_records
             WHERE id = ?"
        );
        $statement->execute([$recordId]);

        $message = 'Employment record deleted successfully.';
        $messageType = 'danger';
    }
}

$search = trim($_GET['search'] ?? '');
$employmentStatus = $_GET['employment_status'] ?? '';
$recordStatus = $_GET['record_status'] ?? 'active';

$sql = "SELECT
            er.id,
            er.user_id,
            er.employee_number,
            er.date_hired,
            er.employment_status,
            er.salary_type,
            er.salary_amount,
            er.contract_end_date,
            er.emergency_contact_name,
            er.emergency_contact_number,
            er.record_status,
            u.fullname,
            u.email,
            sp.specialty,
            sp.branch
        FROM employment_records er
        JOIN users u ON u.id = er.user_id
        LEFT JOIN staff_profiles sp ON sp.user_id = u.id
        WHERE u.role = 'staff'";

$params = [];

if ($search !== '') {
    $sql .= " AND (
        u.fullname LIKE :search_name
        OR u.email LIKE :search_email
        OR er.employee_number LIKE :search_employee_number
        OR sp.specialty LIKE :search_specialty
    )";

    $term = '%' . $search . '%';

    $params['search_name'] = $term;
    $params['search_email'] = $term;
    $params['search_employee_number'] = $term;
    $params['search_specialty'] = $term;
}

if (in_array($employmentStatus, ['full_time', 'part_time', 'contractual'], true)) {
    $sql .= " AND er.employment_status = :employment_status";
    $params['employment_status'] = $employmentStatus;
}

if (in_array($recordStatus, ['active', 'archived'], true)) {
    $sql .= " AND er.record_status = :record_status";
    $params['record_status'] = $recordStatus;
}

$sql .= " ORDER BY er.date_hired DESC, er.id DESC";

$statement = $pdo->prepare($sql);
$statement->execute($params);
$records = $statement->fetchAll();

include '../includes/superadmin-header.php';
?>

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>

    <main class="content">
        <div class="page-header employment-page-header">
            <div>
                <h1 class="gold-title">Employment Records</h1>
                <p>Maintain staff hiring details, compensation information, and emergency contacts.</p>
            </div>

            <a class="btn btn-gold" href="add-employment-record.php">
                Add Employment Record
            </a>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="card employment-filter-card">
            <div class="employment-section-heading">
                <h2>Search &amp; Filter</h2>
                <p>Find employment records using staff information or record status.</p>
            </div>

            <form class="employment-filter-form" method="GET">
                <div class="filter-field">
                    <label for="search">Search</label>
                    <input
                        id="search"
                        name="search"
                        type="text"
                        value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Staff name, email, employee number, or specialty"
                    >
                </div>

                <div class="filter-field">
                    <label for="employment_status">Employment Status</label>
                    <select id="employment_status" name="employment_status">
                        <option value="">All employment types</option>
                        <option value="full_time" <?= $employmentStatus === 'full_time' ? 'selected' : '' ?>>Full Time</option>
                        <option value="part_time" <?= $employmentStatus === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                        <option value="contractual" <?= $employmentStatus === 'contractual' ? 'selected' : '' ?>>Contractual</option>
                    </select>
                </div>

                <div class="filter-field">
                    <label for="record_status">Record Status</label>
                    <select id="record_status" name="record_status">
                        <option value="active" <?= $recordStatus === 'active' ? 'selected' : '' ?>>Active Records</option>
                        <option value="archived" <?= $recordStatus === 'archived' ? 'selected' : '' ?>>Archived Records</option>
                    </select>
                </div>

                <div class="employment-filter-actions">
                    <button class="btn btn-gold" type="submit">Filter</button>
                    <a class="btn btn-light" href="employment-records.php">Reset</a>
                </div>
            </form>
        </section>

        <section class="card employment-table-card">
            <div class="employment-table-heading">
                <h2>Staff Employment Information</h2>
                <p><?= count($records) ?> employment record(s) found.</p>
            </div>

            <div class="table-wrap">
                <table class="employment-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Employment</th>
                            <th>Date Hired</th>
                            <th>Compensation</th>
                            <th>Contract End</th>
                            <th>Emergency Contact</th>
                            <th>Record Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (!$records): ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                No employment records found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <td>
                                    <div class="employment-primary-cell">
                                        <strong>
                                            <?= htmlspecialchars($record['fullname'], ENT_QUOTES, 'UTF-8') ?>
                                        </strong>

                                        <span>
                                            <?= htmlspecialchars($record['employee_number'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>

                                        <small>
                                            <?= htmlspecialchars($record['specialty'] ?? 'Unassigned specialty', ENT_QUOTES, 'UTF-8') ?>
                                            &middot;
                                            <?= htmlspecialchars($record['branch'] ?? 'Main Branch', ENT_QUOTES, 'UTF-8') ?>
                                        </small>
                                    </div>
                                </td>

                                <td>
                                    <span class="employment-type-badge">
                                        <?= htmlspecialchars(
                                            ucfirst(str_replace('_', ' ', $record['employment_status'])),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= date('M d, Y', strtotime($record['date_hired'])) ?>
                                </td>

                                <td>
                                    <div class="employment-compensation">
                                        <strong>
                                            ₱<?= number_format((float) $record['salary_amount'], 2) ?>
                                        </strong>

                                        <span>
                                            <?= htmlspecialchars(
                                                ucfirst(str_replace('_', ' ', $record['salary_type'])),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <?= !empty($record['contract_end_date'])
                                        ? date('M d, Y', strtotime($record['contract_end_date']))
                                        : '—' ?>
                                </td>

                                <td>
                                    <div class="employment-contact-cell">
                                        <strong>
                                            <?= htmlspecialchars($record['emergency_contact_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                        </strong>

                                        <span>
                                            <?= htmlspecialchars($record['emergency_contact_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="employment-record-badge employment-record-<?= $record['record_status'] ?>">
                                        <?= ucfirst($record['record_status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="employment-action-group">
                                        <a
                                            class="employment-action-btn employment-action-edit"
                                            href="edit-employment-record.php?id=<?= (int) $record['id'] ?>"
                                        >
                                            Edit
                                        </a>

                                        <?php if ($record['record_status'] === 'active'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="record_id" value="<?= (int) $record['id'] ?>">
                                                <input type="hidden" name="action" value="archive">

                                                <button class="employment-action-btn employment-action-archive" type="submit">
                                                    Archive
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST">
                                                <input type="hidden" name="record_id" value="<?= (int) $record['id'] ?>">
                                                <input type="hidden" name="action" value="restore">

                                                <button class="employment-action-btn employment-action-restore" type="submit">
                                                    Restore
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('Delete this employment record permanently?');"
                                        >
                                            <input type="hidden" name="record_id" value="<?= (int) $record['id'] ?>">
                                            <input type="hidden" name="action" value="delete">

                                            <button class="employment-action-btn employment-action-delete" type="submit">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
