<?php
// ============================================================
// EMPLOYEE ATTENDANCE (TIME IN / TIME OUT)
// ============================================================

require_once('../config/database.php');
require_once('../config/session.php');

requireLogin();

$user = getCurrentUser();

$today = date('Y-m-d');

$message = '';
$error = '';

// ============================================================
// GET TODAY ATTENDANCE
// ============================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM attendance
    WHERE employee_id = ? AND date = ?
");

$stmt->execute([$user['id'], $today]);

$attendance = $stmt->fetch();

// ============================================================
// TIME IN
// ============================================================

if (isset($_POST['time_in'])) {

    if ($attendance) {

        $error = "Already timed in today.";

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO attendance (
                employee_id,
                date,
                time_in,
                status
            )
            VALUES (
                ?, ?, NOW(), 'present'
            )
        ");

        $stmt->execute([
            $user['id'],
            $today
        ]);

        $message = "Time In successful!";

        header("Refresh:1");
    }
}

// ============================================================
// REFRESH ATTENDANCE DATA
// ============================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM attendance
    WHERE employee_id = ? AND date = ?
");

$stmt->execute([
    $user['id'],
    $today
]);

$attendance = $stmt->fetch();

// ============================================================
// TIME OUT
// ============================================================

if (isset($_POST['time_out'])) {

    if (!$attendance) {

        $error = "You need to Time In first.";

    } elseif ($attendance['time_out']) {

        $error = "Already timed out.";

    } else {

        $stmt = $pdo->prepare("
            UPDATE attendance
            SET
                time_out = NOW(),
                hours_worked = TIMESTAMPDIFF(HOUR, time_in, NOW())
            WHERE id = ?
        ");

        $stmt->execute([
            $attendance['id']
        ]);

        $message = "Time Out successful!";

        header("Refresh:1");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - Payroll System</title>

    <!-- SAME DASHBOARD CSS -->
    <link rel="stylesheet" href="../assets/dashboard.css">

    <style>

        .attendance-card {
            max-width: 600px;
            margin-top: 20px;
        }

        .attendance-info {
            margin-bottom: 20px;
        }

        .attendance-info p {
            margin-bottom: 10px;
            font-size: 16px;
        }

        .attendance-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .attendance-buttons form {
            display: inline;
        }

        .btn-disabled {
            background: #ccc !important;
            cursor: not-allowed;
            color: #666 !important;
        }

        .status-working {
            color: #28a745;
            font-weight: bold;
        }

        .status-notin {
            color: #dc3545;
            font-weight: bold;
        }

    </style>
</head>

<body class="dashboard">

<!-- SIDEBAR -->
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

        <li class="nav-item">
            <a href="../includes/attendanceEmployee.php" class="nav-link active">
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
                <i>💰</i> Payslip
            </a>
        </li>

        <?php if ($user['is_hr'] || $user['is_admin']): ?>

            <li class="nav-item"
                style="margin-top: 20px; padding: 10px 20px; color: rgba(255,255,255,0.5); font-size: 12px; font-weight: bold;">
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

        <strong>
            <?php echo htmlspecialchars($user['name']); ?>
        </strong>

        <small>
            <?php echo htmlspecialchars($user['email']); ?>
        </small>

        <?php if ($user['is_admin']): ?>

            <span class="badge badge-admin">ADMIN</span>

        <?php elseif ($user['is_hr']): ?>

            <span class="badge badge-hr">HR</span>

        <?php endif; ?>

    </div>

</aside>

<!-- MAIN CONTENT -->
<main class="main-content">

    <!-- TOPBAR -->
    <div class="topbar">

        <h1>My Attendance</h1>

        <div>
            <?php echo date('l, F d, Y'); ?>
        </div>

    </div>

    <!-- ALERTS -->
    <?php if ($message): ?>

        <div class="alert alert-success">
            <?php echo $message; ?>
        </div>

    <?php endif; ?>

    <?php if ($error): ?>

        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>

    <?php endif; ?>

    <!-- ATTENDANCE CARD -->
    <div class="card attendance-card">

        <div class="card-header">
            <h3 class="card-title">⏰ Attendance Tracker</h3>
        </div>

        <div class="attendance-info">

            <?php if (!$attendance): ?>

                <p>
                    <strong>Status:</strong>
                    <span class="status-notin">
                        Not Timed In
                    </span>
                </p>

            <?php else: ?>

                <p>
                    <strong>Time In:</strong>
                    <?php echo date('h:i A', strtotime($attendance['time_in'])); ?>
                </p>

                <?php if ($attendance['time_out']): ?>

                    <p>
                        <strong>Time Out:</strong>
                        <?php echo date('h:i A', strtotime($attendance['time_out'])); ?>
                    </p>

                    <p>
                        <strong>Hours Worked:</strong>
                        <?php echo $attendance['hours_worked']; ?> hrs
                    </p>

                    <p>
                        <strong>Status:</strong>
                        Completed Shift
                    </p>

                <?php else: ?>

                    <p>
                        <strong>Status:</strong>
                        <span class="status-working">
                            Currently Working
                        </span>
                    </p>

                <?php endif; ?>

            <?php endif; ?>

        </div>

        <!-- BUTTONS -->
        <div class="attendance-buttons">

            <!-- TIME IN BUTTON -->
            <form method="POST">

                <?php if (!$attendance): ?>

                    <button
                        type="submit"
                        name="time_in"
                        class="btn btn-primary"
                    >
                        🟢 Time In
                    </button>

                <?php else: ?>

                    <button
                        type="button"
                        class="btn btn-disabled"
                        disabled
                    >
                        🟢 Time In
                    </button>

                <?php endif; ?>

            </form>

            <!-- TIME OUT BUTTON -->
            <form method="POST">

                <?php if ($attendance && !$attendance['time_out']): ?>

                    <button
                        type="submit"
                        name="time_out"
                        class="btn btn-secondary"
                    >
                        🔴 Time Out
                    </button>

                <?php else: ?>

                    <button
                        type="button"
                        class="btn btn-disabled"
                        disabled
                    >
                        🔴 Time Out
                    </button>

                <?php endif; ?>

            </form>

        </div>

    </div>

</main>

</body>
</html>