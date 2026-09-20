<?php
session_start();
require_once "db_connect.php";

// Ensure only admin can send warnings
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $message = trim($_POST['warning_message'] ?? '');

    if (!$user_id || $message === '') {
        $_SESSION['flash_error'] = "Missing user or message.";
        header("Location: admin_dashboard.php?tab=finduser");
        exit();
    }

    // Create warnings table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS warnings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            admin_id INT,
            message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $stmt = $pdo->prepare("INSERT INTO warnings (user_id, admin_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $_SESSION['user_id'], $message]);

    $_SESSION['flash_success'] = "Warning sent successfully to user ID $user_id.";
    header("Location: admin_dashboard.php?tab=finduser");
    exit();
}
?>
