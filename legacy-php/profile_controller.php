<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
  header("Location: login.php");
  exit();
}
$user_id = $_SESSION['user_id'];

// Helper: server-side reverse-geocode (Nominatim) — returns display_name or null
function reverse_geocode($lat, $lon){
  $url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=" . urlencode($lat) . "&lon=" . urlencode($lon);
  $opts = ['http'=>['header'=>"User-Agent: GovConnectApp/1.0\r\nConnection: close\r\n", 'timeout'=>8]];
  $ctx = stream_context_create($opts);
  $resp = @file_get_contents($url, false, $ctx);
  if(!$resp) return null;
  $j = json_decode($resp, true);
  return $j['display_name'] ?? null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $phone = trim($_POST['phone'] ?? '');
  $location_text = trim($_POST['location'] ?? '');
  $latitude = $_POST['latitude'] ?? null;
  $longitude = $_POST['longitude'] ?? null;

  // Normalize coordinates (if blank set null)
  if ($latitude === '') $latitude = null;
  if ($longitude === '') $longitude = null;

  // If client supplied numeric coords but location_text looks like coords (or empty) then server reverse-geocode
  if ($latitude && $longitude && (preg_match('/^\s*-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?\s*$/', $location_text) || $location_text === '')) {
    $rv = reverse_geocode($latitude, $longitude);
    if ($rv) $location_text = $rv;
    else $location_text = sprintf('Lat: %.6f, Lon: %.6f', (float)$latitude, (float)$longitude);
  }

  // File upload handling (profile_pics folder)
  $profilePicName = null;
  if (!empty($_FILES['profile_pic']['name']) && is_uploaded_file($_FILES['profile_pic']['tmp_name'])) {
    $allowed_ext = ['jpg','jpeg','png','webp'];
    $file = $_FILES['profile_pic'];
    if ($file['error'] === UPLOAD_ERR_OK) {
      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, $allowed_ext)) {
        $_SESSION['error'] = "Unsupported image format. Use jpg, png or webp.";
        header("Location: profile.php"); exit();
      }
      if ($file['size'] > 2 * 1024 * 1024) {
        $_SESSION['error'] = "Image too large (max 2MB).";
        header("Location: profile.php"); exit();
      }

      $uploadDir = __DIR__ . "/profile_pics/";
      if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
      $basename = "user_{$user_id}_" . time() . "." . $ext;
      $target = $uploadDir . $basename;
      if (move_uploaded_file($file['tmp_name'], $target)) {
        $profilePicName = $basename;

        // Remove previous file if exists
        $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $old = $stmt->fetchColumn();
        if ($old && file_exists($uploadDir . $old)) {
          @unlink($uploadDir . $old);
        }
      } else {
        $_SESSION['error'] = "Failed to move uploaded file.";
        header("Location: profile.php"); exit();
      }
    } else {
      $_SESSION['error'] = "Upload error. Try again.";
      header("Location: profile.php"); exit();
    }
  }

  // Update DB (conditionally include profile_pic)
  try {
    if ($profilePicName) {
      $stmt = $pdo->prepare("UPDATE users SET phone = ?, location = ?, latitude = ?, longitude = ?, profile_pic = ? WHERE user_id = ?");
      $stmt->execute([$phone, $location_text, $latitude, $longitude, $profilePicName, $user_id]);
    } else {
      $stmt = $pdo->prepare("UPDATE users SET phone = ?, location = ?, latitude = ?, longitude = ? WHERE user_id = ?");
      $stmt->execute([$phone, $location_text, $latitude, $longitude, $user_id]);
    }
    $_SESSION['success'] = "Profile updated successfully.";
  } catch (Exception $e) {
    $_SESSION['error'] = "Failed to update profile.";
  }

  header("Location: profile.php");
  exit();
}

// Not a POST -> redirect back
header("Location: profile.php");
exit();
