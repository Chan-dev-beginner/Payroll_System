<?php
// ============================================================
// DATABASE & SESSION
// ============================================================
require_once('../config/database.php');
require_once('../config/session.php');

requireHR(); // Only HR/Admin

// CURRENT USER
$user = getCurrentUser();


$message = '';
$error = '';

//FETCH LEAVE REQUEST
$stmt = $pdo->query("
    SELECT * FROM leave_requests
");

$leave_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leaves</title>

    <link rel="stylesheet" href="../assets/dashboard.css">
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
            <a href="attendanceEmployee.php" class="nav-link">
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

        <?php if ($user['is_hr'] || $user['is_admin']): ?>

        <li class="nav-item" style="margin-top:20px; padding:10px 20px; color:rgba(255,255,255,.5); font-size:12px; font-weight:bold;">
            HR MANAGEMENT
        </li>

        <li class="nav-item">
            <a href="employee.php" class="nav-link active">
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

        <li class="nav-item" style="margin-top:20px;">
            <a href="logout.php" class="nav-link">
                <i>🚪</i> Logout
            </a>
        </li>

    </ul>

    <div class="user-info">

        <strong> <?php echo htmlspecialchars($user['name']); ?> </strong>
        <small> <?php echo htmlspecialchars($user['email']); ?> </small>

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
        <h1>Leave Approval Management</h1>
    </div>

    <div class="card">

        <!-- ALERTS -->
        <?php if($message): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee ID</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Total Days</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>

<?php foreach($leave_requests as $leave): ?>

<tr>

    <td><?php echo $leave['id']; ?></td>

    <td><?php echo $leave['employee_id']; ?></td>

    <td><?php echo $leave['leave_type_id']; ?></td>

    <td><?php echo $leave['start_date']; ?></td>

    <td><?php echo $leave['end_date']; ?></td>

    <td><?php echo $leave['total_days']; ?></td>

    <td><?php echo htmlspecialchars($leave['reason']); ?></td>

    <td>
        <span class="status status-<?php echo $leave['status']; ?>">
            <?php echo ucfirst($leave['status']); ?>
        </span>
    </td>

    <td>

        <?php if($leave['status'] == 'pending'): ?>

            <a href="approve_leave.php?id=<?php echo $leave['id']; ?>"
               class="btn btn-success btn-sm">
               Approve
            </a>

            <a href="reject_leave.php?id=<?php echo $leave['id']; ?>"
               class="btn btn-danger btn-sm">
               Reject
            </a>

        <?php else: ?>

            Done

        <?php endif; ?>

    </td>

</tr>

<?php endforeach; ?>

</tbody>
    </div>
    

    </table>

 </main>






</body>
</html>