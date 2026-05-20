<?php
// ============================================================
// ATTENDANCE PAGE
// ============================================================

require_once('../config/database.php');
require_once('../config/session.php');

// Must be logged in
requireLogin();

$user = getCurrentUser();

// ============================================================
// GET ATTENDANCE RECORDS
// ============================================================

$stmt = $pdo->prepare("
    SELECT attendance.*, employees.firstname, employees.lastname
    FROM attendance
    JOIN employees ON attendance.employee_id = employees.id
    WHERE attendance.employee_id = ?
    ORDER BY attendance.date DESC
");
$stmt->execute([$user['id']]);
$attendance_records = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - Payroll System</title>
    <link rel="stylesheet" href="../assets/attendance_hr.css">
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
                <a href="attendance_hr.php" class="nav-link active">
                    <i>⏰</i> Attendance
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

        <!-- TOPBAR -->
        <div class="topbar">
            <h1>Attendance Report</h1>

            <div>
                <?php echo date('l, F d, Y'); ?>
            </div>
        </div>

        <!-- ATTENDANCE TABLE -->
        <div class="card">

            <div class="card-header">
                <h3 class="card-title">⏰ Attendance Records</h3>
            </div>

            <?php if (empty($attendance_records)): ?>

                <p class="text-muted">No attendance records found.</p>

            <?php else: ?>

            <div class="table-container">

                <table>

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Hours Worked</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($attendance_records as $attendance): ?>

                        <tr>

                            <td>
                                <?php echo $attendance['id']; ?>
                            </td>

                            <td>
                                <?php echo date('F d, Y', strtotime($attendance['date'])); ?>
                            </td>

                            <td>
                                <?php echo $attendance['time_in'] ? date('h:i A', strtotime($attendance['time_in'])) : '-'; ?>
                            </td>

                            <td>
                                <?php echo $attendance['time_out'] ? date('h:i A', strtotime($attendance['time_out'])) : '-'; ?>
                            </td>

                            <td>
                                <?php echo $attendance['hours_worked']; ?> hrs
                            </td>

                            <td>

                                <?php if ($attendance['status'] == 'present'): ?>

                                    <span class="status status-present">
                                        Present
                                    </span>

                                <?php elseif ($attendance['status'] == 'absent'): ?>

                                    <span class="status status-absent">
                                        Absent
                                    </span>

                                <?php elseif ($attendance['status'] == 'late'): ?>

                                    <span class="status status-late">
                                        Late
                                    </span>

                                <?php else: ?>

                                    <span class="status">
                                        <?php echo ucfirst($attendance['status']); ?>
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?php echo date('F d, Y h:i A', strtotime($attendance['created_at'])); ?>
                            </td>

                        </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <?php endif; ?>

        </div>
        
            <!-- FILTER SECTION -->
    <div class="card mb-20">

        <form method="GET">

            <div class="form-row">

                <!-- Department -->
                <div class="form-group">
                    <label>Department</label>

                    <select name="department">
                        <option value="">All Departments</option>
                        <option value="IT">IT</option>
                        <option value="HR">HR</option>
                        <option value="Finance">Finance</option>
                    </select>
                </div>

                <!-- Shift -->
                <div class="form-group">
                    <label>Shift</label>

                    <select name="shift">
                        <option value="">All Shifts</option>
                        <option value="Morning">Morning</option>
                        <option value="Night">Night</option>
                    </select>
                </div>

                <!-- Year -->
                <div class="form-group">
                    <label>Year</label>

                    <select name="year">
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                    </select>
                </div>

                <!-- Month -->
                <div class="form-group">
                    <label>Month</label>

                    <select name="month">
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>

                <!-- Button -->
                <div class="form-group">
                    <label style="visibility: hidden;">Button</label>

                    <button type="submit" class="btn btn-success">
                        Show Report
                    </button>
                </div>

            </div>

        </form>

    </div>


    <!-- ATTENDANCE TABLE -->
    <div class="card">

        <!-- TOP TABLE CONTROLS -->
        <div class="table-top">

            <div>
                Show

                <select>
                    <option>10</option>
                    <option>20</option>
                    <option>30</option>
                </select>

                entries
            </div>

            <div>
                Search:
                <input type="text" placeholder="Search employee">
            </div>

        </div>

        <!-- TABLE -->
        <div class="attendance-table-container">

            <table class="attendance-table">

                <thead>

                    <tr>

                        <th>Employee ID</th>
                        <th>Employee</th>

                        <?php for ($day = 1; $day <= 31; $day++): ?>
                            <th><?php echo str_pad($day, 2, '0', STR_PAD_LEFT); ?></th>
                        <?php endfor; ?>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($attendance_records as $attendance): ?>

                    <tr>

                        <td>
                            <?php echo $attendance['employee_id']; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($attendance['firstname'] . ' ' . $attendance['lastname']); ?>
                        </td>

                            <?php
                            $attendance_day = date('d', strtotime($attendance['date']));
                            ?>

                            <?php for ($day = 1; $day <= 31; $day++): ?>

                                <td>
                                    <?php if ((int)$attendance_day == $day): ?>

                                        <?php if ($attendance['status'] == 'present'): ?>
                                            <span class="present-cell">P</span>

                                        <?php elseif ($attendance['status'] == 'late'): ?>
                                            <span class="late-cell">L</span>

                                        <?php elseif ($attendance['status'] == 'absent'): ?>
                                            <span class="absent-cell">A</span>

                                        <?php else: ?>
                                            -
                                        <?php endif; ?>

                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>

                            <?php endfor; ?>

                    </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

    </main>

</body>
</html>