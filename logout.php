<?php
// logout.php - User logout functionality
// Handles user logout and session cleanup

require_once 'includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Log the logout action
if (isLoggedIn()) {
    error_log("User logged out: " . $_SESSION['email']);
}

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login page with success message
header("Location: /login.php?message=logged_out");
exit;
?>
