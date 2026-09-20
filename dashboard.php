<?php
session_start();

// If user is not logged in, redirect to login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

// Redirect user based on role
switch ($role) {
    case 'user':
        header("Location: user_dashboard.php");
        exit();
    case 'response':
        header("Location: response_dashboard.php");
        exit();
    case 'admin':
        header("Location: admin_dashboard.php");
        exit();
    default:
        // If role is invalid, destroy session and force login again
        session_destroy();
        header("Location: login.php?error=invalid_role");
        exit();
}
