<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        header("Location: profile.php?error=Passwords do not match");
        exit();
    }

    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id=?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($current, $row['password'])) {
        header("Location: profile.php?error=Invalid current password");
        exit();
    }

    $hashed = password_hash($new, PASSWORD_BCRYPT);
    $upd = $pdo->prepare("UPDATE users SET password=? WHERE user_id=?");
    $upd->execute([$hashed, $user_id]);

    header("Location: profile.php?success=Password updated");
    exit();
}
