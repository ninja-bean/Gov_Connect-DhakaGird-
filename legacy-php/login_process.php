<?php
session_start();
require_once "db_connect.php"; // should create $pdo (PDO instance)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$role = $_POST['role'] ?? 'user';
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    header("Location: login.php?error=" . urlencode("Please enter both email and password."));
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT user_id, name, email, password, role, status FROM users WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: login.php?error=" . urlencode("Invalid login credentials."));
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        header("Location: login.php?error=" . urlencode("Invalid login credentials."));
        exit;
    }

    // Check status if Response Team
    if ($role === 'response' && $user['status'] !== 'active') {
        header("Location: login.php?error=" . urlencode("Your Response Team account is still pending admin approval."));
        exit;
    }

    // ✅ Login successful
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['name'];

    header("Location: dashboard.php");
    exit;

} catch (PDOException $e) {
    header("Location: login.php?error=" . urlencode("Server error, please try again later."));
    exit;
}
