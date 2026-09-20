<?php
// resolve_location.php
// Accepts POST lat and lon and returns JSON with address using Nominatim
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error'=>'Method not allowed']);
    exit;
}
$lat = $_POST['lat'] ?? null;
$lon = $_POST['lon'] ?? null;
if (!$lat || !$lon) {
    http_response_code(400);
    echo json_encode(['error'=>'Missing lat or lon']);
    exit;
}
$lat = urlencode($lat);
$lon = urlencode($lon);
$url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$lat}&lon={$lon}&zoom=18&addressdetails=1";
$opts = [
  "http" => [
    "method" => "GET",
    "header" => "User-Agent: GovConnect/1.0\r\n"
  ]
];
$context = stream_context_create($opts);
$result = @file_get_contents($url, false, $context);
if ($result === false) {
    http_response_code(502);
    echo json_encode(['error'=>'Failed to fetch from geocoding service']);
    exit;
}
$data = json_decode($result, true);
if (!$data) {
    http_response_code(502);
    echo json_encode(['error'=>'Invalid response from geocoding service']);
    exit;
}
$display = $data['display_name'] ?? null;
echo json_encode(['address'=>$display, 'raw'=>$data]);
