<?php
require_once('../config/database.php');
require_once('../config/session.php');

requireLogin();

$user = getCurrentUser();

$error = "";
$success = "";


$conn = mysqli_connect("localhost", "root", "", "payroll_system");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $employee_id   = $user['id'];
    $employee_name = $user['name'];
    $leave_type_id = intval($_POST['leave_type_id']);
    $start_date    = $_POST['start_date'];
    $end_date      = $_POST['end_date'];
    $reason        = mysqli_real_escape_string($conn, $_POST['reason']);

    // calculate total days
    $start = new DateTime($start_date);
    $end   = new DateTime($end_date);
    $interval = $start->diff($end);
    $total_days = $interval->days + 1;

    if (empty($leave_type_id) || empty($start_date) || empty($end_date)) {
        $error = "Please fill all required fields.";
    } else {

        $sql = "INSERT INTO leave_requests 
                (employee_id, leave_type_id, start_date, end_date, total_days, reason, status)
                VALUES
                ('$employee_id', '$leave_type_id', '$start_date', '$end_date', '$total_days', '$reason', 'pending')";

        if (mysqli_query($conn, $sql)) {
            $success = "Leave request submitted successfully!";
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System</title>

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
        
        form { max-width: 500px; }
        label { display: block; margin-top: 10px; }
        input, select, textarea { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 15px; padding: 10px 20px; }
        .error { color: red; }
        .success { color: green; }
        
    </style>
</head>

<body class="dashboard">

<!-- ============================================================ -->
<!-- SIDEBAR (UNCHANGED - FULL DASHBOARD FEATURES) -->
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

            <li class="nav-item" style="margin-top: 20px; padding: 10px 20px; color: rgba(255,255,255,0.5); font-size: 12px;">
                HR MANAGEMENT
            </li>

            <li class="nav-item"><a href="employee.php" class="nav-link">👥 Employees</a></li>
            <li class="nav-item"><a href="attendance_hr.php" class="nav-link">📋 Attendance</a></li>
            <li class="nav-item"><a href="manage_leaves.php" class="nav-link">✅ Leave Approval</a></li>
            <li class="nav-item"><a href="manage_incentives.php" class="nav-link">🎁 Incentives</a></li>
            <li class="nav-item"><a href="payroll.php" class="nav-link">📊 Payroll</a></li>

        <?php endif; ?>

        <li class="nav-item" style="margin-top: 20px;">
            <a href="logout.php" class="nav-link">🚪 Logout</a>
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




<!-- MAIN CONTENT -->
<main class="main-content">
    <div class="topbar">
        <h2>Leave Request Form</h2>
        <div><?= date('l, F d, Y'); ?></div>
    </div>

    <div class="card attendance-card">

        <form method="POST">

        <label>Name</label>
        <input name="name" required>

        <label>Leave Type</label>
        <select name="leave_type_id" required>
            <option value="">Select Leave Type</option>
            <option value="1">Vacation Leave</option>
            <option value="2">Sick Leave</option>
            <option value="3">Emergency Leave</option>
            <option value="4">Unpaid Leave</option>
            <option value="5">Maternity Leave</option>
        </select>

        <label>Start Date</label>
        <input type="date" name="start_date" required>

        <label>End Date</label>
        <input type="date" name="end_date" required>

        <label>Reason</label>
        <textarea name="reason" required></textarea>

        <button type="submit">Submit Leave Request</button>

    </form>

    </div>
</main>


</body>
</html>

