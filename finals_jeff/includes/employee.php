<?php
// ============================================================
// EMPLOYEES MANAGEMENT (HR Only)
// ============================================================

require_once('../config/database.php');
require_once('../config/session.php');

requireHR(); // Only HR and Admin can access

$message = '';
$error = '';

// ============================================================
// HANDLE ADD NEW EMPLOYEE
// ============================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    
    $employee_id = 'EMP' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = trim($_POST['phone']);
    $role_id = $_POST['role_id'];
    $department_id = $_POST['department_id'];
    $shift_id = $_POST['shift_id'];
    $hire_date = $_POST['hire_date'];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO employees 
            (employee_id, firstname, lastname, email, password, phone, role_id, department_id, shift_id, hire_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $employee_id, $firstname, $lastname, $email, $password, 
            $phone, $role_id, $department_id, $shift_id, $hire_date
        ]);
        
        $message = "Employee added successfully! Employee ID: " . $employee_id;
    } catch (PDOException $e) {
        $error = "Error adding employee: " . $e->getMessage();
    }
}

// ============================================================
// HANDLE UPDATE EMPLOYEE
// ============================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    
    $id = $_POST['employee_db_id'];
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role_id = $_POST['role_id'];
    $department_id = $_POST['department_id'];
    $shift_id = $_POST['shift_id'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE employees 
            SET firstname = ?, lastname = ?, email = ?, phone = ?, 
                role_id = ?, department_id = ?, shift_id = ?, status = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $firstname, $lastname, $email, $phone, 
            $role_id, $department_id, $shift_id, $status, $id
        ]);
        
        $message = "Employee updated successfully!";
    } catch (PDOException $e) {
        $error = "Error updating employee: " . $e->getMessage();
    }
}

// ============================================================
// GET ALL EMPLOYEES
// ============================================================
$stmt = $pdo->query("
    SELECT e.*, r.role_name, r.monthly_salary, 
           d.department_name, s.shift_name
    FROM employees e
    JOIN roles r ON e.role_id = r.id
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN shifts s ON e.shift_id = s.id
    ORDER BY e.created_at DESC
");
$employees = $stmt->fetchAll();

// Get dropdown data
$roles = $pdo->query("SELECT * FROM roles ORDER BY level DESC")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();
$shifts = $pdo->query("SELECT * FROM shifts ORDER BY shift_name")->fetchAll();

include 'includes/header.php';
?>

<h2>👥 Employee Management</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Employees</h3>
        <button class="btn btn-primary" onclick="openModal('addModal')">+ Add New Employee</button>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Shift</th>
                    <th>Monthly Salary</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($emp['employee_id']); ?></strong></td>
                    <td><?php echo htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']); ?></td>
                    <td><?php echo htmlspecialchars($emp['email']); ?></td>
                    <td><?php echo htmlspecialchars($emp['role_name']); ?></td>
                    <td><?php echo htmlspecialchars($emp['department_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($emp['shift_name'] ?? 'N/A'); ?></td>
                    <td>₱<?php echo number_format($emp['monthly_salary'], 2); ?></td>
                    <td>
                        <span class="status status-<?php echo $emp['status']; ?>">
                            <?php echo ucfirst($emp['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary" 
                                    onclick='editEmployee(<?php echo json_encode($emp); ?>)'>
                                Edit
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD EMPLOYEE MODAL -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Employee</h3>
            <button class="close-btn" onclick="closeModal('addModal')">&times;</button>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="firstname" required>
                </div>
                
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="lastname" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role_id" required>
                        <option value="">Select Role</option>
                        <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>">
                            <?php echo htmlspecialchars($role['role_name']); ?> 
                            - ₱<?php echo number_format($role['monthly_salary'], 0); ?>/month
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Department *</label>
                    <select name="department_id" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>">
                            <?php echo htmlspecialchars($dept['department_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Shift *</label>
                    <select name="shift_id" required>
                        <option value="">Select Shift</option>
                        <?php foreach ($shifts as $shift): ?>
                        <option value="<?php echo $shift['id']; ?>">
                            <?php echo htmlspecialchars($shift['shift_name']); ?> 
                            (<?php echo $shift['time_in'] . ' - ' . $shift['time_out']; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Hire Date *</label>
                    <input type="date" name="hire_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Add Employee</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT EMPLOYEE MODAL -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Employee</h3>
            <button class="close-btn" onclick="closeModal('editModal')">&times;</button>
        </div>
        
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="employee_db_id" id="edit_id">
            
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="firstname" id="edit_firstname" required>
                </div>
                
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="lastname" id="edit_lastname" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" id="edit_email" required>
            </div>
            
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" id="edit_phone">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role_id" id="edit_role_id" required>
                        <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>">
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Department *</label>
                    <select name="department_id" id="edit_department_id" required>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>">
                            <?php echo htmlspecialchars($dept['department_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Shift *</label>
                    <select name="shift_id" id="edit_shift_id" required>
                        <?php foreach ($shifts as $shift): ?>
                        <option value="<?php echo $shift['id']; ?>">
                            <?php echo htmlspecialchars($shift['shift_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" id="edit_status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Employee</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Open modal
function openModal(modalId) {
    document.getElementById(modalId).classList.add('show');
}

// Close modal
function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

// Edit employee - populate form
function editEmployee(emp) {
    document.getElementById('edit_id').value = emp.id;
    document.getElementById('edit_firstname').value = emp.firstname;
    document.getElementById('edit_lastname').value = emp.lastname;
    document.getElementById('edit_email').value = emp.email;
    document.getElementById('edit_phone').value = emp.phone || '';
    document.getElementById('edit_role_id').value = emp.role_id;
    document.getElementById('edit_department_id').value = emp.department_id;
    document.getElementById('edit_shift_id').value = emp.shift_id;
    document.getElementById('edit_status').value = emp.status;
    
    openModal('editModal');
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
    }
}
</script>

<?php include 'includes/footer.php'; ?>