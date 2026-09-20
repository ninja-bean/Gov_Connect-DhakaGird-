<?php
session_start();
require_once "db_connect.php"; // must provide $pdo

// Only logged-in users with role 'user' can submit
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: user_dashboard.php");
    exit();
}

$user_id    = $_SESSION['user_id'];
$category   = trim($_POST['category'] ?? '');
$description= trim($_POST['description'] ?? '');
$suggestion = trim($_POST['suggestion'] ?? '');
$lat        = trim($_POST['latitude'] ?? '');
$lng        = trim($_POST['longitude'] ?? '');
$location_name = trim($_POST['location_name'] ?? '');

// Allowed categories must match DB
$allowed = ['police','medical','fire','gov','other'];
if (!in_array($category, $allowed)) {
    $_SESSION['flash_error'] = "Invalid category selected.";
    header("Location: user_dashboard.php"); exit();
}

if ($lat === '' || $lng === '') {
    $_SESSION['flash_error'] = "Please pick a location (Use Current or Pick on Map).";
    header("Location: user_dashboard.php"); exit();
}

// If location_name empty or looks like coords, try server-side reverse geocode (fallback)
if ($location_name === '' || preg_match('/^\s*-?\d+(\.\d+)?,\s*-?\d+(\.\d+)?\s*$/', $location_name)) {
    // Attempt server-side reverse geocoding using Nominatim (best-effort)
    $location_name = null;
    $safeLat = urlencode($lat);
    $safeLng = urlencode($lng);
    $url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$safeLat}&lon={$safeLng}";
    $opts = [
        "http" => [
            "header" => "User-Agent: GovConnect/1.0\r\n"
        ]
    ];
    $ctx = stream_context_create($opts);
    $resp = @file_get_contents($url, false, $ctx);
    if ($resp !== false) {
        $j = json_decode($resp, true);
        if (!empty($j['display_name'])) {
            $location_name = $j['display_name'];
        }
    }
    if (!$location_name) {
        // fallback to coordinate string
        $location_name = $lat . ', ' . $lng;
    }
}

// Insert into database — store location_name into both location & location_name columns (so UI reads readable text)
try {
    $sql = "INSERT INTO problems (user_id, category, description, suggestion, location, location_name, latitude, longitude)
            VALUES (:user_id, :category, :description, :suggestion, :location, :location_name, :lat, :lng)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':category' => $category,
        ':description' => $description,
        ':suggestion' => $suggestion,
        ':location' => $location_name,
        ':location_name' => $location_name,
        ':lat' => $lat,
        ':lng' => $lng
    ]);
    $_SESSION['flash_success'] = "Report submitted successfully.";
} catch (PDOException $e) {
    error_log("submit_report.php DB error: " . $e->getMessage());
    $_SESSION['flash_error'] = "Failed to submit report. Please try again.";
}

header("Location: user_dashboard.php");
exit();
