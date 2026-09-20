<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['change_password'])) {
    $user_id = $_SESSION['user_id'];
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($current) || empty($new) || empty($confirm)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: change_password.php");
        exit();
    }

    if ($new !== $confirm) {
        $_SESSION['error'] = "New passwords do not match.";
        header("Location: change_password.php");
        exit();
    }

    // fetch user
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current, $user['password'])) {
        $_SESSION['error'] = "Current password is incorrect.";
        header("Location: change_password.php");
        exit();
    }

    // update
    $hashed = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->execute([$hashed, $user_id]);

    $_SESSION['success'] = "Password updated successfully!";
    header("Location: change_password.php");
    exit();
} else {
    header("Location: change_password.php");
    exit();
}
