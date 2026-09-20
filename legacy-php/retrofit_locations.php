<?php
require_once "db_connect.php";

// Get problems where latitude/longitude are NULL but location looks like coordinates
$stmt = $pdo->query("SELECT problem_id, location FROM problems WHERE (latitude IS NULL OR longitude IS NULL)");

$updateStmt = $pdo->prepare("UPDATE problems SET location = ?, latitude = ?, longitude = ? WHERE problem_id = ?");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $id = $row['problem_id'];
    $loc = $row['location'];

    // Check if location is in "lat,lng" format
    if (preg_match('/^\s*(-?\d+\.\d+),\s*(-?\d+\.\d+)\s*$/', $loc, $matches)) {
        $lat = $matches[1];
        $lng = $matches[2];

        // Call OpenStreetMap Nominatim API for reverse geocoding
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lng";
        $options = [
            "http" => [
                "header" => "User-Agent: GovConnectApp/1.0\r\n"
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        $place = "$lat, $lng"; // fallback
        if ($response !== false) {
            $data = json_decode($response, true);
            if (isset($data['display_name'])) {
                $place = $data['display_name'];
            }
        }

        // Update row
        $updateStmt->execute([$place, $lat, $lng, $id]);
        echo "Updated problem_id $id → $place <br>";
    }
}

echo "✅ Retrofit completed!";
