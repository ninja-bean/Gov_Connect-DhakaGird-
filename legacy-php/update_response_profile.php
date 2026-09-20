<?php
session_start();
require_once "db_connect.php";

// Check role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'response') {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize inputs
        $name            = trim($_POST['name'] ?? '');
        $category        = trim($_POST['category'] ?? '');
        $incharge_name   = trim($_POST['incharge_name'] ?? '');
        $incharge_email  = trim($_POST['incharge_email'] ?? '');
        $incharge_phone  = trim($_POST['incharge_phone'] ?? '');
        $employee_number = intval($_POST['employee_number'] ?? 0);
        $location        = trim($_POST['location'] ?? '');
        $latitude        = trim($_POST['latitude'] ?? '');
        $longitude       = trim($_POST['longitude'] ?? '');

        // Validation
        if (!$name || !$category || !$incharge_name || !$incharge_email || !$incharge_phone || !$employee_number || !$location) {
            throw new Exception("All fields are required.");
        }

        // Handle profile picture upload
        $profilePic = null;
        if (!empty($_FILES['profile_pic']['name'])) {
            $uploadDir = __DIR__ . "/profile_pics/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $fileName = basename($_FILES['profile_pic']['name']);
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];

            if (!in_array($ext, $allowed)) {
                throw new Exception("Invalid file type. Allowed: jpg, jpeg, png, webp.");
            }
            if ($_FILES['profile_pic']['size'] > 2*1024*1024) {
                throw new Exception("File too large. Max 2MB.");
            }

            $newName = "team_".$user_id."_".time().".".$ext;
            $targetFile = $uploadDir.$newName;

            if (!move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
                throw new Exception("Failed to upload profile picture.");
            }
            $profilePic = $newName;
        }

        // Build SQL update
        $sql = "UPDATE users 
                   SET name=?, category=?, incharge_name=?, incharge_email=?, 
                       incharge_phone=?, employee_number=?, location=?, 
                       latitude=?, longitude=?";
        $params = [$name,$category,$incharge_name,$incharge_email,$incharge_phone,
                   $employee_number,$location,$latitude,$longitude];

        if ($profilePic) {
            $sql .= ", profile_pic=?";
            $params[] = $profilePic;
        }

        $sql .= " WHERE user_id=?";
        $params[] = $user_id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['success'] = "Profile updated successfully.";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Redirect back
header("Location: response_profile.php");
exit();
