<?php
session_start();
require_once "db_connect.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$request_id = $_POST['request_id'] ?? null;
$user_id = $_POST['user_id'] ?? null;
$action = $_POST['action'] ?? '';

if (!$request_id || !$user_id) {
    $_SESSION['flash_error'] = "Invalid request.";
    header("Location: admin_dashboard.php?tab=finduser");
    exit();
}

if ($action === 'approve_unban') {
    $pdo->prepare("UPDATE users SET is_banned=0, ban_until=NULL, ban_reason=NULL WHERE user_id=?")->execute([$user_id]);
    $pdo->prepare("UPDATE unban_requests SET status='approved' WHERE id=?")->execute([$request_id]);
    $_SESSION['flash_success'] = "User has been successfully unbanned.";
} elseif ($action === 'reject_unban') {
    $pdo->prepare("UPDATE unban_requests SET status='rejected' WHERE id=?")->execute([$request_id]);
    $_SESSION['flash_error'] = "Unban appeal rejected.";
}

header("Location: admin_dashboard.php?tab=finduser");
exit();
?>
