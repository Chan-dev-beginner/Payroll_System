<?php
// ============================================================
// PAYROLL FUNCTIONS - Salary Calculations
// ============================================================

require_once 'database.php';

// ============================================================
// CALCULATE EMPLOYEE PAYROLL FOR A MONTH
// ============================================================
function calculatePayroll($employee_id, $month_year) {
    global $pdo;
    
    // Get employee details
    $stmt = $pdo->prepare("
        SELECT e.*, r.monthly_salary, r.hourly_rate 
        FROM employees e 
        JOIN roles r ON e.role_id = r.id 
        WHERE e.id = ?
    ");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        return false;
    }
    
    // Get month start and end dates
    $start_date = date('Y-m-01', strtotime($month_year));
    $end_date = date('Y-m-t', strtotime($month_year));
    
    // 1. COUNT ATTENDANCE DAYS
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as days_present,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as days_absent,
            SUM(hours_worked) as total_hours
        FROM attendance 
        WHERE employee_id = ? 
        AND date BETWEEN ? AND ?
    ");
    $stmt->execute([$employee_id, $start_date, $end_date]);
    $attendance = $stmt->fetch();
    
    // 2. COUNT APPROVED LEAVES
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN lt.is_paid = 1 THEN lr.total_days ELSE 0 END) as paid_leaves,
            SUM(CASE WHEN lt.is_paid = 0 THEN lr.total_days ELSE 0 END) as unpaid_leaves
        FROM leave_requests lr
        JOIN leave_types lt ON lr.leave_type_id = lt.id
        WHERE lr.employee_id = ?
        AND lr.status = 'approved'
        AND lr.start_date >= ? AND lr.end_date <= ?
    ");
    $stmt->execute([$employee_id, $start_date, $end_date]);
    $leaves = $stmt->fetch();
    
    // 3. GET APPROVED INCENTIVES
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as total_incentives 
        FROM incentives 
        WHERE employee_id = ? 
        AND month_year = ?
        AND status = 'approved'
    ");
    $stmt->execute([$employee_id, $month_year]);
    $incentives = $stmt->fetch();
    
    // 4. CALCULATE SALARY
    $days_worked = $attendance['days_present'] ?? 0;
    $days_absent = $attendance['days_absent'] ?? 0;
    $paid_leaves = $leaves['paid_leaves'] ?? 0;
    $unpaid_leaves = $leaves['unpaid_leaves'] ?? 0;
    $total_hours = $attendance['total_hours'] ?? 0;
    
    // Working days in month (assuming 22 working days)
    $working_days = 22;
    $daily_rate = $employee['monthly_salary'] / $working_days;
    
    // Basic salary = (days worked + paid leaves) × daily rate
    $basic_salary = ($days_worked + $paid_leaves) * $daily_rate;
    
    // Total incentives
    $total_incentives = $incentives['total_incentives'] ?? 0;
    
    // Deductions (simplified: 10% tax + 5% contributions)
    $gross_pay = $basic_salary + $total_incentives;
    $total_deductions = $gross_pay * 0.15; // 15% total deductions
    
    // Net pay
    $net_pay = $gross_pay - $total_deductions;
    
    // 5. SAVE TO PAYROLL TABLE
    $stmt = $pdo->prepare("
        INSERT INTO payroll 
        (employee_id, month_year, basic_salary, total_incentives, total_deductions, 
         days_worked, days_absent, paid_leaves, unpaid_leaves, total_hours, 
         gross_pay, net_pay, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')
        ON DUPLICATE KEY UPDATE
            basic_salary = VALUES(basic_salary),
            total_incentives = VALUES(total_incentives),
            total_deductions = VALUES(total_deductions),
            days_worked = VALUES(days_worked),
            days_absent = VALUES(days_absent),
            paid_leaves = VALUES(paid_leaves),
            unpaid_leaves = VALUES(unpaid_leaves),
            total_hours = VALUES(total_hours),
            gross_pay = VALUES(gross_pay),
            net_pay = VALUES(net_pay)
    ");
    
    $stmt->execute([
        $employee_id,
        $month_year,
        $basic_salary,
        $total_incentives,
        $total_deductions,
        $days_worked,
        $days_absent,
        $paid_leaves,
        $unpaid_leaves,
        $total_hours,
        $gross_pay,
        $net_pay
    ]);
    
    return [
        'basic_salary' => $basic_salary,
        'total_incentives' => $total_incentives,
        'total_deductions' => $total_deductions,
        'days_worked' => $days_worked,
        'days_absent' => $days_absent,
        'paid_leaves' => $paid_leaves,
        'unpaid_leaves' => $unpaid_leaves,
        'gross_pay' => $gross_pay,
        'net_pay' => $net_pay
    ];
}

// ============================================================
// CALCULATE HOURS WORKED
// ============================================================
function calculateHours($time_in, $time_out) {
    $start = strtotime($time_in);
    $end = strtotime($time_out);
    
    // If time_out is earlier than time_in, add 24 hours (night shift)
    if ($end < $start) {
        $end += 86400; // Add 24 hours in seconds
    }
    
    $hours = ($end - $start) / 3600; // Convert seconds to hours
    return round($hours, 2);
}

// ============================================================
// GET WORKING DAYS IN MONTH
// ============================================================
function getWorkingDays($month_year) {
    $start = new DateTime(date('Y-m-01', strtotime($month_year)));
    $end = new DateTime(date('Y-m-t', strtotime($month_year)));
    
    $working_days = 0;
    while ($start <= $end) {
        $day_of_week = $start->format('N'); // 1 = Monday, 7 = Sunday
        if ($day_of_week < 6) { // Monday to Friday
            $working_days++;
        }
        $start->modify('+1 day');
    }
    
    return $working_days;
}
?>