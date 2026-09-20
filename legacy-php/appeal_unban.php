<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['user_id'])) exit();

$user_id = $_SESSION['user_id'];
$reason = trim($_POST['reason'] ?? '');

if ($reason !== '') {
    $stmt = $pdo->prepare("INSERT INTO unban_requests (user_id, reason) VALUES (?, ?)");
    $stmt->execute([$user_id, $reason]);
    $_SESSION['flash_success'] = "Your unban appeal has been sent.";
}
header("Location: user_dashboard.php");
exit();
?>
