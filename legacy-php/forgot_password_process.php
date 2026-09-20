<?php
session_start();
require_once "db_connect.php"; // your PDO connection file

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot_password.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
if (!$email) {
    header("Location: forgot_password.php?error=" . urlencode("Please enter your email."));
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: forgot_password.php?error=" . urlencode("No account found with that email."));
        exit;
    }

    // --- Simulated reset link ---
    // In real case: generate token, save to DB, send email with link
    $resetLink = "http://localhost/govconnect/reset_password.php?token=EXAMPLETOKEN";

    // For now, just success message
    header("Location: forgot_password.php?success=" . urlencode("Password reset link sent to your email."));
    exit;

} catch (PDOException $e) {
    header("Location: forgot_password.php?error=" . urlencode("Server error, please try again later."));
    exit;
}
