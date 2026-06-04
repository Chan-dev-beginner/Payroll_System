# Payroll System Project Overview

## Purpose
This PHP/MySQL web application is designed for small HR teams to manage employees, attendance, leave, incentives, and payroll in a single dashboard.

## Main Functionality
- User authentication with role-based access control
- Employee attendance tracking
- Leave request submission and HR approval
- Incentive type management
- Payroll generation, payroll record management, and net pay calculation
- Payslip viewing and XML export

## Key Components
### Entry Point
- `index.php`
  - redirects to `includes/login.php`

### Configuration
- `config/database.php`
  - sets up a PDO connection to the MySQL database
- `config/session.php`
  - starts sessions, checks login, checks HR/admin access, retrieves current user

### Main Pages
- `includes/login.php`
  - user login page with credential validation
- `includes/dashboard.php`
  - dashboard summaries and navigation
- `includes/attendanceEmployee.php`
  - attendance interface for employees
- `includes/attendance_hr.php`
  - attendance management for HR
- `includes/leave.php`
  - employee leave request submission
- `includes/manage_leaves.php`
  - HR leave approval and rejection
- `includes/employee.php`
  - employee management for HR/admin
- `includes/manage_incentives.php`
  - incentive type management
- `includes/payroll.php`
  - payroll generation, update, finalize, and delete actions
- `includes/payslip.php`
  - employee payslip view
- `includes/export_employees_xml.php`
  - XML export of employee data
- `includes/export_payslip_xml.php`
  - XML export for payroll/payslip data

## Roles and Access
### Employee
- login
- view dashboard
- track attendance
- request leave
- view own payslip

### HR/Admin
- all employee actions
- manage employee records
- approve/reject leave requests
- manage attendance records
- manage incentive types
- generate and finalize payroll
- view payroll records

## Data Flow
1. User logs in using `includes/login.php`
2. Session begins through `config/session.php`
3. Dashboard data is loaded from the database
4. Employees submit attendance and leave requests
5. HR approves leaves and manages incentives
6. Payroll is generated in `includes/payroll.php` using:
   - attendance records
   - approved leave requests
   - incentive totals
   - role salary data
   - simplified deductions
7. Employees view payroll results in `includes/payslip.php`

## Database and Deployment
- `payroll_system.sql`
  - database schema and seed data for the application
- Recommended deployment:
  1. Place project folder in XAMPP `htdocs`
  2. Import `payroll_system.sql` to MySQL
  3. Configure credentials in `config/database.php`
  4. Open `http://localhost/Payroll_system/`

## Folder Structure
- `config/` — shared database and session helpers
- `includes/` — web pages and business logic
- `assets/` — CSS styles
- `database/` — database-related files or exports
- `payroll_system.sql` — database schema

## Notes
- The app uses role-based restrictions so HR/admin users see more management pages.
- Payroll generation produces draft payroll records that can be finalized.
- The system is built for a local PHP environment, typically XAMPP.
