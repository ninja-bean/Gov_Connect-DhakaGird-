<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'response') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $problem_id = (int)($_POST['problem_id'] ?? 0);
    $status_update = $_POST['status_update'] ?? '';
    $members = (int)($_POST['members'] ?? 0); // Members input may not exist when resolving
    $report = $_POST['report'] ?? null;
    $user_id = $_SESSION['user_id'];

    // Fetch current team info
    $stmt = $pdo->prepare("SELECT total_members, busy_members FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$team) {
        die("Team not found.");
    }

    $available = $team['total_members'] - $team['busy_members'];

    // Check availability only if starting working
    if ($status_update === 'working') {
        if ($available <= 0) {
            echo "<script>alert('No members available!'); window.history.back();</script>";
            exit();
        }

        if ($members > $available) {
            echo "<script>alert('Not enough available members! Only $available members free.'); window.history.back();</script>";
            exit();
        }
    }

    try {
        if ($status_update === 'working') {
            // Save working member count and update problem status
            $stmt = $pdo->prepare("UPDATE problems SET status = 'working', working_members = ? WHERE problem_id = ? AND assigned_to = ?");
            $stmt->execute([$members, $problem_id, $user_id]);

            // Adjust busy_members
            $new_busy = $team['busy_members'] + $members;
            if ($new_busy > $team['total_members']) $new_busy = $team['total_members'];

            $stmt = $pdo->prepare("UPDATE users SET busy_members = ? WHERE user_id = ?");
            $stmt->execute([$new_busy, $user_id]);

        } elseif ($status_update === 'resolved') {
            // Get current working_members for this problem to release
            $stmt = $pdo->prepare("SELECT working_members FROM problems WHERE problem_id = ? AND assigned_to = ?");
            $stmt->execute([$problem_id, $user_id]);
            $problem = $stmt->fetch(PDO::FETCH_ASSOC);
            $members_to_release = (int)($problem['working_members'] ?? 0);

            // Update problem as resolved, free members, and save report
            $stmt = $pdo->prepare("UPDATE problems SET status = 'resolved', working_members = 0, report = ? WHERE problem_id = ? AND assigned_to = ?");
            $stmt->execute([$report, $problem_id, $user_id]);

            // Release members
            $new_busy = max(0, $team['busy_members'] - $members_to_release);
            $stmt = $pdo->prepare("UPDATE users SET busy_members = ? WHERE user_id = ?");
            $stmt->execute([$new_busy, $user_id]);

        } else {
            // Other status updates
            $stmt = $pdo->prepare("UPDATE problems SET status = ? WHERE problem_id = ? AND assigned_to = ?");
            $stmt->execute([$status_update, $problem_id, $user_id]);
        }

        echo "<script>alert('Problem updated successfully.'); window.location.href='response_dashboard.php';</script>";
        exit();

    } catch (Exception $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit();
    }
}
?>
