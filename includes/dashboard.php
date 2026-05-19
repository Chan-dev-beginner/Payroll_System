<?php
// ============================================================
// DASHBOARD - Main Page
// ============================================================

require_once('../config/database.php');
require_once('../config/session.php');

// Must be logged in to access this page
requireLogin();

$user = getCurrentUser();

// Get statistics for dashboard
// 1. Count total employees
$stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'");
$total_employees = $stmt->fetchColumn();

// 2. Count pending leave requests
$stmt = $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'pending'");
$pending_leaves = $stmt->fetchColumn();

// 3. Count pending incentives
$stmt = $pdo->query("SELECT COUNT(*) FROM incentive_types");
$incentive_types_count = $stmt->fetchColumn();

// 4. Get my latest payslip
$stmt = $pdo->prepare("
    SELECT * FROM payroll 
    WHERE employee_id = ? 
    ORDER BY month_year DESC 
    LIMIT 1
");
$stmt->execute([$user['id']]);
$my_payslip = $stmt->fetch();

// 5. Get my recent leave requests
$stmt = $pdo->prepare("
    SELECT lr.*, lt.leave_name, lt.is_paid
    FROM leave_requests lr
    JOIN leave_types lt ON lr.leave_type_id = lt.id
    WHERE lr.employee_id = ?
    ORDER BY lr.created_at DESC
    LIMIT 5
");
$stmt->execute([$user['id']]);
$my_leaves = $stmt->fetchAll();

// 6. Recent leave requests (for HR view)
$stmt = $pdo->query("
    SELECT lr.*, e.firstname, e.lastname, lt.leave_name, lt.is_paid
    FROM leave_requests lr
    JOIN employees e ON lr.employee_id = e.id
    JOIN leave_types lt ON lr.leave_type_id = lt.id
    ORDER BY lr.created_at DESC
    LIMIT 10
");
$recent_leaves = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Payroll System</title>
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
                <a href="dashboard.php" class="nav-link active">
                    <i>🏠</i> Dashboard
                </a>
            </li>
            
            <li class="nav-item">

                <?php if ($user['is_hr'] || $user['is_admin']): ?>

                    <a href="attendance.php" class="nav-link">
                        <i>⏰</i> Attendance
                    </a>

                <?php else: ?>

                    <a href="attendance_hr.php" class="nav-link">
                        <i>⏰</i> My Attendance
                    </a>

                <?php endif; ?>

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
            <li class="nav-item" style="margin-top: 20px; padding: 10px 20px; color: rgba(255,255,255,0.5); font-size: 12px; font-weight: bold;">
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
            <strong><?php echo htmlspecialchars($user['name']); ?></strong>
            <small><?php echo htmlspecialchars($user['email']); ?></small>
            <?php if ($user['is_admin']): ?>
                <span class="badge badge-admin">ADMIN</span>
            <?php elseif ($user['is_hr']): ?>
                <span class="badge badge-hr">HR</span>
            <?php endif; ?>
        </div>
    </aside>
    
    <!-- MAIN CONTENT -->
    <main class="main-content">
        
        <!-- TOP BAR -->
        <div class="topbar">
            <h1>Dashboard</h1>
            <div>
                <?php echo date('l, F d, Y'); ?>
            </div>
        </div>
        
        <!-- STATISTICS CARDS -->
        <div class="stats-grid">
            <?php if ($user['is_hr'] || $user['is_admin']): ?>
            <!-- For HR/Admin -->
            <div class="stat-card blue">
                <span class="stat-icon">👥</span>
                <h3>Total Employees</h3>
                <div class="stat-value"><?php echo $total_employees; ?></div>
            </div>
            
            <div class="stat-card orange">
                <span class="stat-icon">🏖️</span>
                <h3>Pending Leaves</h3>
                <div class="stat-value"><?php echo $pending_leaves; ?></div>
            </div>
            
            <div class="stat-card green">
                <span class="stat-icon">🎁</span>
                <h3>Pending Incentives</h3>
                <div class="stat-value"><?php echo $incentive_types_count; ?></div>
            </div>
            <?php endif; ?>
            
            <!-- My Payslip -->
            <?php if ($my_payslip): ?>
            <div class="stat-card green">
                <span class="stat-icon">💰</span>
                <h3>My Latest Net Pay</h3>
                <div class="stat-value">₱<?php echo number_format($my_payslip['net_pay'], 2); ?></div>
                <small class="text-muted"><?php echo date('F Y', strtotime($my_payslip['month_year'])); ?></small>
            </div>
            
            <div class="stat-card blue">
                <span class="stat-icon">📅</span>
                <h3>Days Worked</h3>
                <div class="stat-value"><?php echo $my_payslip['days_worked']; ?></div>
                <small class="text-muted">Last month</small>
            </div>
            <?php endif; ?>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            
            <!-- MY LEAVE REQUESTS -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🏖️ My Leave Requests</h3>
                    <a href="leave.php" class="btn btn-primary btn-sm">Request Leave</a>
                </div>
                
                <?php if (empty($my_leaves)): ?>
                    <p class="text-muted">No leave requests yet.</p>
                <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_leaves as $leave): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($leave['leave_name']); ?>
                                    <?php if ($leave['is_paid']): ?>
                                        <span class="badge badge-hr">Paid</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $leave['start_date'] . ' to ' . $leave['end_date']; ?></td>
                                <td><?php echo $leave['total_days']; ?></td>
                                <td>
                                    <span class="status status-<?php echo $leave['status']; ?>">
                                        <?php echo ucfirst($leave['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- RECENT LEAVE REQUESTS (HR View) -->
            <?php if ($user['is_hr'] || $user['is_admin']): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📋 Recent Leave Requests</h3>
                    <a href="manage_leaves.php" class="btn btn-secondary btn-sm">View All</a>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Days</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_leaves as $leave): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($leave['firstname'] . ' ' . $leave['lastname']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($leave['leave_name']); ?>
                                    <?php if ($leave['is_paid']): ?>
                                        <span class="badge badge-hr">Paid</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $leave['total_days']; ?></td>
                                <td>
                                    <span class="status status-<?php echo $leave['status']; ?>">
                                        <?php echo ucfirst($leave['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        
    </main>
    
</body>
</html>