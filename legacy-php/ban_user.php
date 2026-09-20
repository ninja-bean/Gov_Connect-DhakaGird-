<?php
session_start();
require_once "db_connect.php";

// Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $action = $_POST['action'] ?? '';
    $search_value = $_POST['search_value'] ?? '';

    if (!$user_id) {
        $_SESSION['flash_error'] = "Missing user ID.";
        header("Location: admin_dashboard.php?tab=finduser&find_user=" . urlencode($search_value));
        exit();
    }

    try {
        if ($action === 'ban_user') {
            $banDays = (int)($_POST['ban_days'] ?? 0);
            $isLifetime = isset($_POST['lifetime']);

            if ($isLifetime) {
                $banUntil = 'permanent';
            } elseif ($banDays > 0) {
                $banUntil = date('Y-m-d H:i:s', strtotime("+$banDays days"));
            } else {
                throw new Exception("Please provide valid ban duration or lifetime option.");
            }

            $stmt = $pdo->prepare("UPDATE users SET is_banned=1, ban_until=? WHERE user_id=?");
            $stmt->execute([$banUntil, $user_id]);

            $_SESSION['flash_success'] = "User banned successfully.";
        }

        elseif ($action === 'unban_user' || $action === 'approve_unban') {
            $stmt = $pdo->prepare("UPDATE users SET is_banned=0, ban_until=NULL WHERE user_id=?");
            $stmt->execute([$user_id]);

            // Remove any pending unban requests
            $pdo->prepare("DELETE FROM unban_requests WHERE user_id=?")->execute([$user_id]);

            $_SESSION['flash_success'] = "User unbanned successfully.";
        }

        elseif ($action === 'reject_unban') {
            $pdo->prepare("DELETE FROM unban_requests WHERE user_id=?")->execute([$user_id]);
            $_SESSION['flash_success'] = "Unban request rejected.";
        }

        header("Location: admin_dashboard.php?tab=finduser&find_user=" . urlencode($search_value));
        exit();

    } catch (Exception $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        header("Location: admin_dashboard.php?tab=finduser&find_user=" . urlencode($search_value));
        exit();
    }
}
?>
