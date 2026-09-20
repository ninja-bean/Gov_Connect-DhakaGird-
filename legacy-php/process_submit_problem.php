<?php
session_start();
require_once "db_connect.php"; // $pdo must exist

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ---------------- SOS quick report ---------------- */
if (isset($_GET['sos']) && $_GET['sos'] == "1") {
    if (!isset($_GET['lat']) || !isset($_GET['lng'])) {
        $_SESSION['flash_error'] = "Location not provided for SOS.";
        header("Location: user_dashboard.php");
        exit();
    }

    $lat = (float)$_GET['lat'];
    $lng = (float)$_GET['lng'];

    // Dhaka bounds
    if ($lat < 23.65 || $lat > 23.90 || $lng < 90.30 || $lng > 90.55) {
        $_SESSION['flash_error'] = "SOS reports allowed only inside Dhaka.";
        header("Location: user_dashboard.php");
        exit();
    }

    // Get readable address
    $location_name = null;
    $url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$lat}&lon={$lng}";
    $opts = ["http" => ["header" => "User-Agent: GovConnect/1.0\r\n"]];
    $ctx = stream_context_create($opts);
    $resp = @file_get_contents($url, false, $ctx);
    if ($resp !== false) {
        $j = json_decode($resp, true);
        if (!empty($j['display_name'])) $location_name = $j['display_name'];
    }

    if (!$location_name) {
        $_SESSION['flash_error'] = "Failed to get location name.";
        header("Location: user_dashboard.php");
        exit();
    }

    try {
        $sql = "INSERT INTO problems 
                (user_id, category, description, suggestion, location, location_name, latitude, longitude, status, priority, media_path, created_at)
                VALUES (:user_id, 'police', 'Emergency SOS triggered by user.', NULL, :loc, :loc_name, :lat, :lng, 'pending', 'medium', NULL, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id'   => $user_id,
            ':loc'       => $location_name,
            ':loc_name'  => $location_name,
            ':lat'       => $lat,
            ':lng'       => $lng
        ]);
        $_SESSION['flash_success'] = "🚨 SOS sent successfully.";
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "DB Error: " . $e->getMessage();
    }

    header("Location: user_dashboard.php");
    exit();
}

/* ---------------- Normal report ---------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: user_dashboard.php");
    exit();
}

$category    = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$suggestion  = trim($_POST['suggestion'] ?? '');
$lat         = trim($_POST['latitude'] ?? '');
$lng         = trim($_POST['longitude'] ?? '');

// Validate category
$allowed = ['police','medical','fire','gov','other'];
if (!in_array($category, $allowed)) {
    $_SESSION['flash_error'] = "Invalid category selected.";
    header("Location: user_dashboard.php");
    exit();
}

// Validate location
if ($lat === '' || $lng === '') {
    $_SESSION['flash_error'] = "Please pick a location.";
    header("Location: user_dashboard.php");
    exit();
}

$latF = (float)$lat;
$lngF = (float)$lng;

// Restrict to Dhaka
if ($latF < 23.65 || $latF > 23.90 || $lngF < 90.30 || $lngF > 90.55) {
    $_SESSION['flash_error'] = "Reports must be inside Dhaka.";
    header("Location: user_dashboard.php");
    exit();
}

// Resolve readable address
$location_name = null;
$url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$latF}&lon={$lngF}";
$opts = ["http" => ["header" => "User-Agent: GovConnect/1.0\r\n"]];
$ctx = stream_context_create($opts);
$resp = @file_get_contents($url, false, $ctx);
if ($resp !== false) {
    $j = json_decode($resp, true);
    if (!empty($j['display_name'])) $location_name = $j['display_name'];
}
if (!$location_name) {
    $_SESSION['flash_error'] = "Failed to resolve location.";
    header("Location: user_dashboard.php");
    exit();
}

/* -------- Media upload -------- */
$uploadedFiles = [];
if (!empty($_FILES['media_files']['name'][0])) {
    $targetDir = __DIR__ . "/problems/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    foreach ($_FILES['media_files']['tmp_name'] as $i => $tmpName) {
        if ($_FILES['media_files']['error'][$i] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['media_files']['name'][$i], PATHINFO_EXTENSION);
            $safeName = uniqid("media_") . "." . strtolower($ext);
            $targetPath = $targetDir . $safeName;
            if (move_uploaded_file($tmpName, $targetPath)) {
                $uploadedFiles[] = $safeName;
            }
        }
    }
}
$mediaField = $uploadedFiles ? implode(",", $uploadedFiles) : null;

/* -------- Insert -------- */
try {
    $sql = "INSERT INTO problems 
            (user_id, category, description, suggestion, location, location_name, latitude, longitude, status, priority, media_path, created_at)
            VALUES (:user_id, :cat, :descr, :sugg, :loc, :loc_name, :lat, :lng, 'pending', 'medium', :media, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id'   => $user_id,
        ':cat'       => $category,
        ':descr'     => $description,
        ':sugg'      => $suggestion,
        ':loc'       => $location_name,
        ':loc_name'  => $location_name,
        ':lat'       => $latF,
        ':lng'       => $lngF,
        ':media'     => $mediaField
    ]);

    $_SESSION['flash_success'] = "✅ Report submitted successfully.";
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "DB Error: " . $e->getMessage();
}

header("Location: user_dashboard.php");
exit();
