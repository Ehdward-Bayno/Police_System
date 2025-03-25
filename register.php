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
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $badge_number = $_POST['badge_number'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate form
        if (empty($name) || empty($email) || empty($badge_number) || empty($password) || empty($confirm_password)) {
            $error = 'All fields are required.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen(string: $password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            // Connect to database
            $db = new Database();
            
            // Check if email already exists
            $existing_user = $db->fetch(
                sql: "SELECT * FROM users WHERE email = :email",
                params: [':email' => $email]
            );
            
            if ($existing_user) {
                $error = 'Email already in use. Please use a different email.';
            } else {
                // Check if badge number already exists
                $existing_badge = $db->fetch(
                    sql: "SELECT * FROM users WHERE badge_number = :badge_number",
                    params: [':badge_number' => $badge_number]
                );
                
                if ($existing_badge) {
                    $error = 'Badge number already in use. Please use a different badge number.';
                } else {
                    // Hash password
                    $hashed_password = hashPassword($password);
                    
                    // Insert user
                    $user_id = $db->insert(
                        sql: "INSERT INTO users (name, email, badge_number, password) VALUES (:name, :email, :badge_number, :password)",
                        params: [
                            ':name' => $name,
                            ':email' => $email,
                            ':badge_number' => $badge_number,
                            ':password' => $hashed_password
                        ]
                    );
                    
                    if ($user_id) {
                        // Set session variables
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_email'] = $email;
                        
                        // Redirect to dashboard
                        redirect('dashboard.php');
                    } else {
                        $error = 'Registration failed. Please try again.';
                    }
                }
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
            <h2 class="fs-4 fw-bold">Create an Account</h2>
            <p class="text-muted">National Police Commission - Criminal Profiling System</p>
        </div>
        
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="John Smith" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="officer@example.com" required>
                </div>
                
                <div class="mb-3">
                    <label for="badge_number" class="form-label">Badge Number</label>
                    <input type="text" class="form-control" id="badge_number" name="badge_number" placeholder="PD-12345" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
        </div>
        
        <div class="card-footer text-center">
            <p class="mb-0 small">Already have an account? <a href="login.php" class="text-primary">Login</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

