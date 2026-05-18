<?php
require_once('../config/database.php');
require_once('../config/session.php');

requireLogin();

$user = getCurrentUser();

if (!$user['is_hr'] && !$user['is_admin']) {
    header("Location: dashboard.php");
    exit();
}

/* ============================================================
   DELETE
============================================================ */
if (isset($_GET['delete'])) {

    $stmt = $pdo->prepare("DELETE FROM incentive_types WHERE id = ?");
    $stmt->execute([$_GET['delete']]);

    header("Location: manage_incentives.php");
    exit();
}

/* ============================================================
   GET EDIT DATA
============================================================ */
$editData = null;

if (isset($_GET['edit'])) {

    $stmt = $pdo->prepare("SELECT * FROM incentive_types WHERE id = ?");
    $stmt->execute([$_GET['edit']]);

    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ============================================================
   ADD INCENTIVE TYPE
============================================================ */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_incentive'])) {

    $stmt = $pdo->prepare("
        INSERT INTO incentive_types (incentive_name, description, amount)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $_POST['incentive_name'],
        $_POST['description'],
        $_POST['amount']
    ]);

    header("Location: manage_incentives.php");
    exit();
}

/* ============================================================
   UPDATE INCENTIVE TYPE
============================================================ */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_incentive'])) {

    $stmt = $pdo->prepare("
        UPDATE incentive_types
        SET incentive_name = ?, description = ?, amount = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['incentive_name'],
        $_POST['description'],
        $_POST['amount'],
        $_POST['id']
    ]);

    header("Location: manage_incentives.php");
    exit();
}

/* ============================================================
   FETCH ALL INCENTIVES
============================================================ */
$incentives = $pdo->query("
    SELECT id, incentive_name, description, amount, created_at
    FROM incentive_types
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Incentives</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="dashboard">

<!-- SIDEBAR (same as dashboard) -->
<aside class="sidebar">
        <div class="sidebar-header">
            <h2>💼 Payroll System</h2>
            <p>HR Management</p>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active">
                    <i>🏠</i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="attendance.php" class="nav-link">
                    <i>⏰</i> My Attendance
                </a>
            </li>
            <li class="nav-item">
                <a href="leave.php" class="nav-link">
                    <i>🏖️</i> Request Leave
                </a>
            </li>
            <li class="nav-item">
                <a href="payslip.php" class="nav-link">
                    <i>💰</i> My Payslip
                </a>
            </li>
            
            <?php if ($user['is_hr'] || $user['is_admin']): ?>
            <li class="nav-item" style="margin-top: 20px; padding: 10px 20px; color: rgba(255,255,255,0.5); font-size: 12px; font-weight: bold;">
                HR MANAGEMENT
            </li>
            <li class="nav-item">
                <a href="employee.php" class="nav-link">
                    <i>👥</i> Employees
                </a>
            </li>
            <li class="nav-item">
                <a href="manage_attendance.php" class="nav-link">
                    <i>📋</i> Attendance
                </a>
            </li>
            <li class="nav-item">
                <a href="manage_leaves.php" class="nav-link">
                    <i>✅</i> Leave Approval
                </a>
            </li>
            <li class="nav-item">
                <a href="manage_incentives.php" class="nav-link">
                    <i>🎁</i> Incentives
                </a>
            </li>
            <li class="nav-item">
                <a href="payroll.php" class="nav-link">
                    <i>📊</i> Payroll
                </a>
            </li>
            <li class="nav-item">
                <a href="settings.php" class="nav-link">
                    <i>⚙️</i> Settings
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item" style="margin-top: 20px;">
                <a href="logout.php" class="nav-link">
                    <i>🚪</i> Logout
                </a>
            </li>
        </ul>
    <div class="user-info">
        <strong><?= htmlspecialchars($user['name']) ?></strong>
        <small><?= htmlspecialchars($user['email']) ?></small>
    </div>

</aside>

<!-- MAIN CONTENT -->
<main class="main-content">

    <!-- TOPBAR -->
    <div class="topbar">
        <h1>🎁 Incentives Management</h1>
        <div><?= date('l, F d, Y') ?></div>
    </div>

    <div class="form-row">

        <!-- FORM -->
        <div class="card">

            <div class="card-header">
                <h3 class="card-title">
                    <?= $editData ? "Edit Incentive" : "Add Incentive" ?>
                </h3>
            </div>

            <form method="POST">

                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Incentive Name</label>
                    <input type="text" name="incentive_name"
                           value="<?= $editData['incentive_name'] ?? '' ?>"
                           required>
                </div>

                <div class="form-group">
                    <label>Amount</label>
                    <input type="number" step="0.01" name="amount"
                           value="<?= $editData['amount'] ?? '' ?>"
                           required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?= $editData['description'] ?? '' ?></textarea>
                </div>

                <button type="submit"
                        name="<?= $editData ? 'update_incentive' : 'add_incentive' ?>"
                        class="btn btn-primary btn-block">

                    <?= $editData ? "✏️ Update Incentive" : "➕ Add Incentive" ?>
                </button>

                <?php if ($editData): ?>
                    <a href="manage_incentives.php" class="btn btn-secondary btn-block">
                        Cancel
                    </a>
                <?php endif; ?>

            </form>

        </div>

        <!-- TABLE -->
        <div class="card">

            <div class="card-header">
                <h3 class="card-title">Incentive Records</h3>
            </div>

            <div class="table-container">

                <table>

                    <thead>
                        <tr>
                            <th>Incentive</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($incentives as $i): ?>
                        <tr>

                            <td><?= htmlspecialchars($i['incentive_name']) ?></td>

                            <td>
                                ₱<?= number_format($i['amount'], 2) ?>
                            </td>

                            <td><?= htmlspecialchars($i['description']) ?></td>

                            <td><?= $i['created_at'] ?></td>

                            <td class="action-buttons">

                                <a href="?edit=<?= $i['id'] ?>"
                                   class="btn btn-warning btn-sm">
                                    Edit
                                </a>

                                <a href="?delete=<?= $i['id'] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Delete this incentive?')">
                                    Delete
                                </a>

                            </td>

                        </tr>
                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</main>

</body>
</html>