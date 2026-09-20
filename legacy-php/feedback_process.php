<?php
// feedback_process.php
session_start();
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);
$problem_id = intval($_POST['problem_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($problem_id <= 0 || $rating < 1 || $rating > 5) {
    header("Location: user_dashboard.php?error=" . urlencode("Invalid input"));
    exit;
}

// ensure problem belongs to user and is resolved
$stmt = $pdo->prepare("SELECT user_id, status FROM problems WHERE problem_id = ? LIMIT 1");
$stmt->execute([$problem_id]);
$row = $stmt->fetch();
if (!$row || $row['user_id'] != $user_id) {
    header("Location: user_dashboard.php?error=" . urlencode("Unauthorized or problem not found"));
    exit;
}
if ($row['status'] !== 'resolved') {
    header("Location: user_dashboard.php?error=" . urlencode("Feedback allowed only after resolution"));
    exit;
}

// check existing feedback
$stmt = $pdo->prepare("SELECT feedback_id FROM feedbacks WHERE problem_id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$problem_id, $user_id]);
if ($stmt->fetchColumn()) {
    header("Location: user_dashboard.php?msg=" . urlencode("Feedback already submitted"));
    exit;
}

// insert feedback
$stmt = $pdo->prepare("INSERT INTO feedbacks (problem_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
$stmt->execute([$problem_id, $user_id, $rating, $comment]);

header("Location: user_dashboard.php?msg=" . urlencode("Thank you — your feedback has been recorded"));
exit;
