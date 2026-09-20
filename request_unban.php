<?php
session_start();
require_once "db_connect.php";

// Ensure only users can appeal
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $appeal_message = trim($_POST['appeal_message'] ?? '');

    if (empty($appeal_message)) {
        $_SESSION['flash_error'] = "Please provide a reason for your unban request.";
        header("Location: submit_problem.php");
        exit();
    }

    // Ensure table for storing appeals exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS unban_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            message TEXT NOT NULL,
            status ENUM('pending','reviewed','approved','rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reviewed_at DATETIME NULL,
            admin_response TEXT NULL,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        )
    ");

    // Check if user already has a pending request
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM unban_requests WHERE user_id = ? AND status = 'pending'");
    $stmt->execute([$user_id]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['flash_error'] = "You already have a pending unban request. Please wait for admin review.";
        header("Location: submit_problem.php");
        exit();
    }

    // Insert new unban request
    $stmt = $pdo->prepare("INSERT INTO unban_requests (user_id, message) VALUES (?, ?)");
    $stmt->execute([$user_id, $appeal_message]);

    $_SESSION['flash_success'] = "Your unban request has been submitted successfully. Please wait for admin review.";
    header("Location: submit_problem.php");
    exit();
} else {
    header("Location: submit_problem.php");
    exit();
}
?>
