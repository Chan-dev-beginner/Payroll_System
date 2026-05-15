<?php
// ============================================================
// LOGIN PAGE
// ============================================================

require_once('../config/database.php');;
require_once '../config/session.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Query database for user
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        // Login successful
        loginUser($user);
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Payroll System</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <h1>💼 Payroll System</h1>
                <p>Human Resource Management</p>
            </div>
            
            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to your account</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required 
                           placeholder="your.email@company.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required 
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Sign In →
                </button>
            </form>
            
            <div class="demo-accounts">
                <p><strong>Demo Accounts:</strong></p>
                <p>Admin: <code>admin@company.com</code> / <code>admin123</code></p>
                <p>Employee: <code>juan@company.com</code> / <code>pass123</code></p>
            </div>
        </div>
    </div>
</body>
</html>