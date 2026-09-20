<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$reason = trim($_POST['reason'] ?? '');

if ($reason === '') {
    $_SESSION['flash_error'] = "Please provide a reason for your unban appeal.";
    header("Location: user_dashboard.php");
    exit();
}

// Ensure table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS unban_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reason TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)");

// Check if already pending
$stmt = $pdo->prepare("SELECT * FROM unban_requests WHERE user_id=? AND status='pending'");
$stmt->execute([$user_id]);
if ($stmt->fetch()) {
    $_SESSION['flash_error'] = "You already have a pending unban request.";
    header("Location: user_dashboard.php");
    exit();
}

// Submit appeal
$stmt = $pdo->prepare("INSERT INTO unban_requests (user_id, reason) VALUES (?, ?)");
$stmt->execute([$user_id, $reason]);

$_SESSION['flash_success'] = "Your unban appeal has been submitted successfully.";
header("Location: user_dashboard.php");
exit();
?>
