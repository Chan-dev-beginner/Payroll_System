<?php
// ============================================================
// ATTENDANCE PAGE (FULL SIDEBAR + FIXED FEATURES)
// ============================================================

require_once('../config/database.php');
require_once('../config/session.php');

requireLogin();

$user = getCurrentUser();

// ============================================================
// FILTER
// ============================================================

$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$all_months = isset($_GET['all_months']);

// ============================================================
// QUERY
// ============================================================

if ($all_months) {

    $stmt = $pdo->prepare("
        SELECT attendance.*, employees.firstname, employees.lastname
        FROM attendance
        JOIN employees ON attendance.employee_id = employees.id
        WHERE attendance.employee_id = ?
        AND YEAR(attendance.date) = ?
        ORDER BY attendance.date DESC
    ");

    $stmt->execute([$user['id'], $year]);

} else {

    $stmt = $pdo->prepare("
        SELECT attendance.*, employees.firstname, employees.lastname
        FROM attendance
        JOIN employees ON attendance.employee_id = employees.id
        WHERE attendance.employee_id = ?
        AND MONTH(attendance.date) = ?
        AND YEAR(attendance.date) = ?
        ORDER BY attendance.date DESC
    ");

    $stmt->execute([$user['id'], $month, $year]);
}

$attendance_records = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - Payroll System</title>

    <link rel="stylesheet" href="../assets/dashboard.css">

    <style>
        .present-cell { color: green; font-weight: bold; }
        .late-cell { color: orange; font-weight: bold; }
        .absent-cell { color: red; font-weight: bold; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
    </style>
</head>

<body class="dashboard">

<!-- ============================================================ -->
<!-- SIDEBAR (FULL COPIED FROM DASHBOARD - NOT REMOVED) -->
<!-- ============================================================ -->
<aside class="sidebar">

    <div class="sidebar-header">
        <h2>💼 Payroll System</h2>
        <p>HR Management</p>
    </div>

    <ul class="nav-menu">

        <li class="nav-item">
            <a href="dashboard.php" class="nav-link">
                <i>🏠</i> Dashboard
            </a>
        </li>

        <!-- ALL USERS FEATURES -->
        <li class="nav-item">
            <a href="../includes/attendanceEmployee.php" class="nav-link active">
                <i>⏰</i> My Attendance
            </a>
        </li>

        <li class="nav-item">
            <a href="leave.php" class="nav-link">
                <i>🏖️</i> My Request Leave
            </a>
        </li>

        <li class="nav-item">
            <a href="payslip.php" class="nav-link">
                <i>💰</i> My Payslip
            </a>
        </li>

        <!-- HR / ADMIN -->
        <?php if ($user['is_hr'] || $user['is_admin']): ?>

            <li class="nav-item" style="margin-top: 20px; padding: 10px 20px; color: rgba(255,255,255,0.5); font-size: 12px;">
                HR MANAGEMENT
            </li>

            <li class="nav-item">
                <a href="employee.php" class="nav-link">
                    <i>👥</i> Employees
                </a>
            </li>

            <li class="nav-item">
                <a href="attendance_hr.php" class="nav-link">
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

        <?php endif; ?>

        <li class="nav-item" style="margin-top: 20px;">
            <a href="logout.php" class="nav-link">
                <i>🚪</i> Logout
            </a>
        </li>

    </ul>

    <div class="user-info">
        <strong><?= htmlspecialchars($user['name']); ?></strong>
        <small><?= htmlspecialchars($user['email']); ?></small>

        <?php if ($user['is_admin']): ?>
            <span class="badge badge-admin">ADMIN</span>
        <?php elseif ($user['is_hr']): ?>
            <span class="badge badge-hr">HR</span>
        <?php endif; ?>
    </div>

</aside>

<!-- ============================================================ -->
<!-- MAIN CONTENT -->
<!-- ============================================================ -->
<main class="main-content">

    <div class="topbar">
        <h1>My Attendance</h1>
        <div><?= date('l, F d, Y'); ?></div>
    </div>

    <!-- FILTER -->
    <div class="card">

        <form method="GET">

            <label>
                <input type="checkbox" name="all_months"
                    <?= $all_months ? 'checked' : '' ?>>
                Show All Months (This Year)
            </label>

            <br><br>

            <label>Month:</label>
            <select name="month">
                <?php for ($m=1; $m<=12; $m++): ?>
                    <option value="<?= str_pad($m,2,'0',STR_PAD_LEFT) ?>"
                        <?= $month == str_pad($m,2,'0',STR_PAD_LEFT) ? 'selected' : '' ?>>
                        <?= date('F', mktime(0,0,0,$m,1)) ?>
                    </option>
                <?php endfor; ?>
            </select>

            <label>Year:</label>
            <select name="year">
                <option value="2024" <?= $year==2024?'selected':'' ?>>2024</option>
                <option value="2025" <?= $year==2025?'selected':'' ?>>2025</option>
                <option value="2026" <?= $year==2026?'selected':'' ?>>2026</option>
            </select>

            <button type="submit" class="btn btn-success">
                Filter
            </button>

        </form>

    </div>

    <!-- TABLE -->
    <div class="card">

        <?php if (empty($attendance_records)): ?>
            <p>No attendance records found.</p>
        <?php else: ?>

        <table>

            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Hours</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($attendance_records as $row): ?>

                <tr>

                    <td><?= date('M d, Y', strtotime($row['date'])) ?></td>

                    <td><?= htmlspecialchars($row['firstname'].' '.$row['lastname']) ?></td>

                    <td><?= $row['time_in'] ? date('h:i A', strtotime($row['time_in'])) : '-' ?></td>

                    <td><?= $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '-' ?></td>

                    <td><?= $row['hours_worked'] ?? 0 ?> hrs</td>

                    <td>
                        <?php if ($row['status'] == 'present'): ?>
                            <span class="present-cell">Present</span>
                        <?php elseif ($row['status'] == 'late'): ?>
                            <span class="late-cell">Late</span>
                        <?php elseif ($row['status'] == 'absent'): ?>
                            <span class="absent-cell">Absent</span>
                        <?php else: ?>
                            <?= ucfirst($row['status']) ?>
                        <?php endif; ?>
                    </td>

                </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

        <?php endif; ?>

    </div>

</main>

</body>
</html>