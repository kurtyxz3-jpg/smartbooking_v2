<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';

$isEdit = true;
$pageTitle = $isEdit ? 'Edit Employment Record' : 'Add Employment Record';
$error = '';

$recordId = $isEdit
    ? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    : null;

$userId = '';
$employeeNumber = '';
$dateHired = date('Y-m-d');
$employmentStatus = 'full_time';
$salaryType = 'monthly';
$salaryAmount = '';
$contractEndDate = '';
$emergencyContactName = '';
$emergencyContactNumber = '';
$emergencyContactRelationship = '';
$governmentIdType = '';
$governmentIdNumber = '';
$notes = '';
$recordStatus = 'active';

$staffUsers = $pdo->query(
    "SELECT u.id, u.fullname, u.email
     FROM users u
     WHERE u.role = 'staff'
     ORDER BY u.fullname ASC"
)->fetchAll();

if ($isEdit) {
    if (!$recordId) {
        header('Location: employment-records.php');
        exit;
    }

    $statement = $pdo->prepare(
        "SELECT *
         FROM employment_records
         WHERE id = ?
         LIMIT 1"
    );
    $statement->execute([$recordId]);
    $record = $statement->fetch();

    if (!$record) {
        header('Location: employment-records.php');
        exit;
    }

    $userId = (string) $record['user_id'];
    $employeeNumber = $record['employee_number'];
    $dateHired = $record['date_hired'];
    $employmentStatus = $record['employment_status'];
    $salaryType = $record['salary_type'];
    $salaryAmount = (string) $record['salary_amount'];
    $contractEndDate = $record['contract_end_date'] ?? '';
    $emergencyContactName = $record['emergency_contact_name'] ?? '';
    $emergencyContactNumber = $record['emergency_contact_number'] ?? '';
    $emergencyContactRelationship = $record['emergency_contact_relationship'] ?? '';
    $governmentIdType = $record['government_id_type'] ?? '';
    $governmentIdNumber = $record['government_id_number'] ?? '';
    $notes = $record['notes'] ?? '';
    $recordStatus = $record['record_status'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (string) filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $employeeNumber = trim($_POST['employee_number'] ?? '');
    $dateHired = $_POST['date_hired'] ?? '';
    $employmentStatus = $_POST['employment_status'] ?? 'full_time';
    $salaryType = $_POST['salary_type'] ?? 'monthly';
    $salaryAmount = trim($_POST['salary_amount'] ?? '');
    $contractEndDate = $_POST['contract_end_date'] ?? '';
    $emergencyContactName = trim($_POST['emergency_contact_name'] ?? '');
    $emergencyContactNumber = trim($_POST['emergency_contact_number'] ?? '');
    $emergencyContactRelationship = trim($_POST['emergency_contact_relationship'] ?? '');
    $governmentIdType = trim($_POST['government_id_type'] ?? '');
    $governmentIdNumber = trim($_POST['government_id_number'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $recordStatus = $_POST['record_status'] ?? 'active';

    if ($userId === '' || $employeeNumber === '' || $dateHired === '') {
        $error = 'Please complete all required employment fields.';
    } elseif (!in_array($employmentStatus, ['full_time', 'part_time', 'contractual'], true)) {
        $error = 'Invalid employment status.';
    } elseif (!in_array($salaryType, ['monthly', 'daily', 'hourly', 'commission'], true)) {
        $error = 'Invalid salary type.';
    } elseif ($salaryAmount === '' || !is_numeric($salaryAmount) || (float) $salaryAmount < 0) {
        $error = 'Please enter a valid salary amount.';
    } elseif (!in_array($recordStatus, ['active', 'archived'], true)) {
        $error = 'Invalid record status.';
    } else {
        $duplicateSql = "SELECT id
                         FROM employment_records
                         WHERE (user_id = :user_id OR employee_number = :employee_number)";

        if ($isEdit) {
            $duplicateSql .= " AND id != :id";
        }

        $duplicateSql .= " LIMIT 1";

        $check = $pdo->prepare($duplicateSql);

        $checkParams = [
            'user_id' => (int) $userId,
            'employee_number' => $employeeNumber,
        ];

        if ($isEdit) {
            $checkParams['id'] = $recordId;
        }

        $check->execute($checkParams);

        if ($check->fetch()) {
            $error = 'This staff member or employee number already has an employment record.';
        } else {
            $params = [
                'user_id' => (int) $userId,
                'employee_number' => $employeeNumber,
                'date_hired' => $dateHired,
                'employment_status' => $employmentStatus,
                'salary_type' => $salaryType,
                'salary_amount' => (float) $salaryAmount,
                'contract_end_date' => $contractEndDate !== '' ? $contractEndDate : null,
                'emergency_contact_name' => $emergencyContactName !== '' ? $emergencyContactName : null,
                'emergency_contact_number' => $emergencyContactNumber !== '' ? $emergencyContactNumber : null,
                'emergency_contact_relationship' => $emergencyContactRelationship !== '' ? $emergencyContactRelationship : null,
                'government_id_type' => $governmentIdType !== '' ? $governmentIdType : null,
                'government_id_number' => $governmentIdNumber !== '' ? $governmentIdNumber : null,
                'notes' => $notes !== '' ? $notes : null,
                'record_status' => $recordStatus,
            ];

            if ($isEdit) {
                $params['id'] = $recordId;

                $statement = $pdo->prepare(
                    "UPDATE employment_records
                     SET user_id = :user_id,
                         employee_number = :employee_number,
                         date_hired = :date_hired,
                         employment_status = :employment_status,
                         salary_type = :salary_type,
                         salary_amount = :salary_amount,
                         contract_end_date = :contract_end_date,
                         emergency_contact_name = :emergency_contact_name,
                         emergency_contact_number = :emergency_contact_number,
                         emergency_contact_relationship = :emergency_contact_relationship,
                         government_id_type = :government_id_type,
                         government_id_number = :government_id_number,
                         notes = :notes,
                         record_status = :record_status
                     WHERE id = :id"
                );
            } else {
                $statement = $pdo->prepare(
                    "INSERT INTO employment_records (
                        user_id,
                        employee_number,
                        date_hired,
                        employment_status,
                        salary_type,
                        salary_amount,
                        contract_end_date,
                        emergency_contact_name,
                        emergency_contact_number,
                        emergency_contact_relationship,
                        government_id_type,
                        government_id_number,
                        notes,
                        record_status
                    ) VALUES (
                        :user_id,
                        :employee_number,
                        :date_hired,
                        :employment_status,
                        :salary_type,
                        :salary_amount,
                        :contract_end_date,
                        :emergency_contact_name,
                        :emergency_contact_number,
                        :emergency_contact_relationship,
                        :government_id_type,
                        :government_id_number,
                        :notes,
                        :record_status
                    )"
                );
            }

            $statement->execute($params);

            header('Location: employment-records.php');
            exit;
        }
    }
}

include '../includes/superadmin-header.php';
?>

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <div>
                <h1 class="gold-title">
                    <?= $isEdit ? 'Edit Employment Record' : 'Add Employment Record' ?>
                </h1>

                <p>
                    <?= $isEdit
                        ? 'Update hiring, compensation, and emergency information.'
                        : 'Create a complete employment record for an existing staff account.' ?>
                </p>
            </div>

            <a class="btn btn-light" href="employment-records.php">
                Back to Records
            </a>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="card employment-form-card">
            <form method="POST">
                <div class="employment-form-section">
                    <h2>Employment Information</h2>

                    <div class="employment-form-grid">
                        <div class="form-group employment-span-2">
                            <label for="user_id">Staff Account</label>
                            <select id="user_id" name="user_id" required>
                                <option value="">Select staff member</option>

                                <?php foreach ($staffUsers as $staffUser): ?>
                                    <option
                                        value="<?= (int) $staffUser['id'] ?>"
                                        <?= (string) $staffUser['id'] === $userId ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars(
                                            $staffUser['fullname'] . ' — ' . $staffUser['email'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="employee_number">Employee Number</label>
                            <input
                                id="employee_number"
                                name="employee_number"
                                type="text"
                                value="<?= htmlspecialchars($employeeNumber, ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="Example: NBW-2026-001"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="date_hired">Date Hired</label>
                            <input
                                id="date_hired"
                                name="date_hired"
                                type="date"
                                value="<?= htmlspecialchars($dateHired, ENT_QUOTES, 'UTF-8') ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="employment_status">Employment Status</label>
                            <select id="employment_status" name="employment_status">
                                <option value="full_time" <?= $employmentStatus === 'full_time' ? 'selected' : '' ?>>Full Time</option>
                                <option value="part_time" <?= $employmentStatus === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                                <option value="contractual" <?= $employmentStatus === 'contractual' ? 'selected' : '' ?>>Contractual</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="record_status">Record Status</label>
                            <select id="record_status" name="record_status">
                                <option value="active" <?= $recordStatus === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="archived" <?= $recordStatus === 'archived' ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="employment-form-section">
                    <h2>Compensation &amp; Contract</h2>

                    <div class="employment-form-grid">
                        <div class="form-group">
                            <label for="salary_type">Salary Type</label>
                            <select id="salary_type" name="salary_type">
                                <option value="monthly" <?= $salaryType === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                <option value="daily" <?= $salaryType === 'daily' ? 'selected' : '' ?>>Daily</option>
                                <option value="hourly" <?= $salaryType === 'hourly' ? 'selected' : '' ?>>Hourly</option>
                                <option value="commission" <?= $salaryType === 'commission' ? 'selected' : '' ?>>Commission</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="salary_amount">Salary Amount</label>
                            <input
                                id="salary_amount"
                                name="salary_amount"
                                type="number"
                                min="0"
                                step="0.01"
                                value="<?= htmlspecialchars($salaryAmount, ENT_QUOTES, 'UTF-8') ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="contract_end_date">Contract End Date</label>
                            <input
                                id="contract_end_date"
                                name="contract_end_date"
                                type="date"
                                value="<?= htmlspecialchars($contractEndDate, ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>
                    </div>
                </div>

                <div class="employment-form-section">
                    <h2>Emergency Contact</h2>

                    <div class="employment-form-grid">
                        <div class="form-group">
                            <label for="emergency_contact_name">Contact Name</label>
                            <input
                                id="emergency_contact_name"
                                name="emergency_contact_name"
                                type="text"
                                value="<?= htmlspecialchars($emergencyContactName, ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="emergency_contact_number">Contact Number</label>
                            <input
                                id="emergency_contact_number"
                                name="emergency_contact_number"
                                type="text"
                                value="<?= htmlspecialchars($emergencyContactNumber, ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="emergency_contact_relationship">Relationship</label>
                            <input
                                id="emergency_contact_relationship"
                                name="emergency_contact_relationship"
                                type="text"
                                value="<?= htmlspecialchars($emergencyContactRelationship, ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>
                    </div>
                </div>

                <div class="employment-form-section">
                    <h2>Government ID &amp; Notes</h2>

                    <div class="employment-form-grid">
                        <div class="form-group">
                            <label for="government_id_type">Government ID Type</label>
                            <input
                                id="government_id_type"
                                name="government_id_type"
                                type="text"
                                value="<?= htmlspecialchars($governmentIdType, ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="Example: SSS, PhilHealth, Pag-IBIG"
                            >
                        </div>

                        <div class="form-group">
                            <label for="government_id_number">Government ID Number</label>
                            <input
                                id="government_id_number"
                                name="government_id_number"
                                type="text"
                                value="<?= htmlspecialchars($governmentIdNumber, ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>

                        <div class="form-group employment-span-2">
                            <label for="notes">Employment Notes</label>
                            <textarea id="notes" name="notes" rows="5"><?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-gold" type="submit">
                        <?= $isEdit ? 'Save Changes' : 'Create Employment Record' ?>
                    </button>

                    <a class="btn btn-light" href="employment-records.php">
                        Cancel
                    </a>
                </div>
            </form>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
