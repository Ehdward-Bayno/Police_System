<?php
require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } else {
            // Connect to database
            $db = new Database();
            
            // Check if user exists
            $user = $db->fetch(
                "SELECT * FROM users WHERE email = :email",
                [':email' => $email]
            );
            
            if ($user && verifyPassword($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Redirect to dashboard
                redirect('dashboard.php');
            } else {
                $error = 'Invalid credentials. Please try again.';
            }
            
            $db->close();
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Include header
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="card auth-card">
        <div class="auth-header">
            <i class="bi bi-shield-lock auth-icon"></i>
            <h2 class="fs-4 fw-bold">National Police Commission</h2>
            <p class="text-muted">Criminal Profiling System</p>
        </div>
        
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="officer@example.com" required>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label for="password" class="form-label">Password</label>
                        <a href="forgot-password.php" class="small text-primary">Forgot password?</a>
                    </div>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
        
        <div class="card-footer text-center">
            <p class="mb-0 small">Don't have an account? <a href="register.php" class="text-primary">Register</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

