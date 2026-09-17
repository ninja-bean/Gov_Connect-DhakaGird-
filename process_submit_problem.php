<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Redirect;
use App\Services\ProblemService;

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db_connect.php'; // $pdo must exist

Auth::requireRole('user');

$service = new ProblemService($pdo);
$userId  = (int) Auth::id();

/* ---------------- SOS quick report (JSON) ---------------- */
if (isset($_POST['action']) && $_POST['action'] === 'sos') {
    header('Content-Type: application/json');

    $lat = filter_var($_POST['lat'] ?? '', FILTER_VALIDATE_FLOAT);
    $lng = filter_var($_POST['lng'] ?? '', FILTER_VALIDATE_FLOAT);

    if ($lat === false || $lng === false) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'GPS location required.']);
        exit;
    }

    try {
        $service->submitSos($userId, $lat, $lng);
        echo json_encode(['status' => 'success']);
    } catch (InvalidArgumentException $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } catch (RuntimeException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to send SOS alert. Please try again.']);
    }
    exit;
}

/* ---------------- Normal report (form post) ---------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Redirect::to('user_dashboard.php');
}

$category    = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$suggestion  = trim($_POST['suggestion'] ?? '');
$lat         = trim($_POST['latitude'] ?? '');
$lng         = trim($_POST['longitude'] ?? '');

if ($lat === '' || $lng === '') {
    Redirect::withError('user_dashboard.php', 'Please pick a location.');
}

$latF = (float) $lat;
$lngF = (float) $lng;

/* -------- Media upload -------- */
$uploadedFiles = [];
if (!empty($_FILES['media_files']['name'][0])) {
    $targetDir = __DIR__ . '/problems/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    foreach ($_FILES['media_files']['tmp_name'] as $i => $tmpName) {
        if ($_FILES['media_files']['error'][$i] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['media_files']['name'][$i], PATHINFO_EXTENSION);
            $safeName = uniqid('media_') . '.' . strtolower($ext);
            if (move_uploaded_file($tmpName, $targetDir . $safeName)) {
                $uploadedFiles[] = $safeName;
            }
        }
    }
}
$mediaField = $uploadedFiles ? implode(',', $uploadedFiles) : null;

try {
    $service->submitReport(
        userId: $userId,
        category: $category,
        description: $description,
        suggestion: $suggestion !== '' ? $suggestion : null,
        lat: $latF,
        lng: $lngF,
        mediaPath: $mediaField
    );
    $_SESSION['flash_success'] = 'Report submitted successfully.';
} catch (InvalidArgumentException $e) {
    $_SESSION['flash_error'] = $e->getMessage();
} catch (RuntimeException $e) {
    $_SESSION['flash_error'] = 'Server error, please try again later.';
}

Redirect::to('user_dashboard.php');