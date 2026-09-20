<?php
session_start();
require_once "db_connect.php";

// Only allow admin or response team
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','response'])) {
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $problem_id = (int)$_POST['problem_id'];

    // Fetch the problem data
    $stmt = $pdo->prepare("SELECT * FROM problems WHERE problem_id=?");
    $stmt->execute([$problem_id]);
    $problem = $stmt->fetch(PDO::FETCH_ASSOC);

    if($problem){
        // Move to deleted_problems table (keep as-is)
        $insert = $pdo->prepare("
            INSERT INTO deleted_problems 
            (problem_id,user_id,category,description,suggestion,location,status,priority,report)
            VALUES (?,?,?,?,?,?,?,?,?)
        ");
        $insert->execute([
            $problem['problem_id'],
            $problem['user_id'],
            $problem['category'],
            $problem['description'],
            $problem['suggestion'],
            $problem['location'],
            $problem['status'],
            $problem['priority'],
            $problem['report'] ?? ''
        ]);

        // Soft delete depending on role
        if($_SESSION['role'] === 'admin'){
            $pdo->prepare("UPDATE problems SET deleted_by_admin = 1 WHERE problem_id=?")->execute([$problem_id]);
        } elseif($_SESSION['role'] === 'response'){
            $pdo->prepare("UPDATE problems SET deleted_by_team = 1 WHERE problem_id=?")->execute([$problem_id]);
        }
    }
}

// Redirect to appropriate dashboard
if($_SESSION['role'] === 'admin'){
    header("Location: admin_dashboard.php");
} else {
    header("Location: response_dashboard.php");
}
exit();
?>
