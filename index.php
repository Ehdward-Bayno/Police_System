<?php
require_once 'includes/config.php';

// Redirect to dashboard if logged in, otherwise to login page
if (isLoggedIn()) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}
?>

