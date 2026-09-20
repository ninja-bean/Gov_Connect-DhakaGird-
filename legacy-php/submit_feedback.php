<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $problem_id = $_POST['problem_id'];
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);

    if (!empty($problem_id) && !empty($rating) && !empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO feedbacks (problem_id, user_id, rating, comment)
                               VALUES (?, ?, ?, ?)");
        $stmt->execute([$problem_id, $user_id, $rating, $comment]);
        $_SESSION['flash_success'] = "Feedback submitted successfully!";
    } else {
        $_SESSION['flash_error'] = "Please fill all fields!";
    }

    header("Location: user_dashboard.php");
    exit;
}
?>
