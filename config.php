<?php
// Application configuration
define('APP_NAME', 'National Police Commission - Criminal Profiling System');
define('APP_URL', 'http://localhost/police_system'); // Change this to your actual URL
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/database/setup.php';

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit;
    }
}

function getUserData() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $db = getDB();
        $stmt = $db->prepare('SELECT id, name, email, badge_number FROM users WHERE id = :id');
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

function displayAlert($message, $type = 'success') {
    return '<div class="alert alert-' . $type . '">' . $message . '</div>';
}
?>

