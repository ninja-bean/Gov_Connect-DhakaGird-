<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Check if user is banned ---
$stmt = $pdo->prepare("SELECT ban_until FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$is_banned = false;
$ban_message = "";
$ban_reason = "";

if ($user && !empty($user['ban_until'])) {
    if ($user['ban_until'] === 'permanent' || strtotime($user['ban_until']) > time()) {
        $is_banned = true;
        if ($user['ban_until'] === 'permanent') {
            $ban_message = "Your account has been permanently banned.";
        } else {
            $until = date("F j, Y, g:i a", strtotime($user['ban_until']));
            $ban_message = "You are temporarily banned until <b>$until</b>.";
        }
        
        // Get ban reason if available
        try {
            $reasonStmt = $pdo->prepare("SELECT ban_reason FROM users WHERE user_id = ?");
            $reasonStmt->execute([$user_id]);
            $reasonData = $reasonStmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($reasonData['ban_reason'])) {
                $ban_reason = $reasonData['ban_reason'];
            }
        } catch (Exception $e) {}
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Problem - DhakaGrid</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

    <style>
        :root {
            --bg: #F8FAFC;
            --surface: #FFFFFF;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --primary: #0F172A; 
            --accent: #3B82F6;
            --danger: #EF4444;
            --success: #10B981;
            --border: #E2E8F0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex; 
            flex-direction: column;
        }

        /* HEADER */
        .header { 
            background: var(--surface); 
            height: 80px; 
            padding: 0 40px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            border-bottom: 1px solid var(--border); 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
        }
        
        .brand { 
            font-size: 1.4rem; 
            font-weight: 800; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        
        .back-btn {
            padding: 10px 20px;
            border-radius: 10px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: var(--bg);
            border-color: var(--primary);
        }

        /* CONTAINER */
        .container { 
            max-width: 900px; 
            margin: 30px auto; 
            padding: 0 40px; 
            width: 100%; 
            flex: 1; 
        }

        /* CARD */
        .card { 
            background: var(--surface); 
            border-radius: 16px; 
            border: 1px solid var(--border); 
            overflow: hidden; 
            margin-bottom: 25px; 
        }
        
        .card-head { 
            padding: 24px 28px; 
            border-bottom: 1px solid var(--border); 
            font-weight: 700; 
            font-size: 1.3rem;
            background: linear-gradient(180deg, #FFFFFF 0%, #FAFAFA 100%);
        }

        .card-body {
            padding: 32px;
        }

        /* BAN MESSAGE */
        .ban-alert {
            background: #FEF2F2;
            border: 2px solid #FCA5A5;
            border-radius: 12px;
            padding: 32px;
            text-align: center;
        }

        .ban-icon {
            font-size: 3.5rem;
            color: var(--danger);
            margin-bottom: 20px;
        }

        .ban-message {
            font-size: 1.15rem;
            color: #991B1B;
            margin-bottom: 16px;
            line-height: 1.6;
        }

        .ban-reason {
            background: rgba(239, 68, 68, 0.1);
            padding: 16px;
            border-radius: 10px;
            margin: 20px 0;
            font-size: 0.95rem;
            color: #7F1D1D;
        }

        /* FORM */
        .form-group {
            margin-bottom: 28px;
        }

        .form-label {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: var(--accent);
            font-size: 1rem;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 14px 16px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-main);
            font-size: 0.95rem;
            font-family: 'Outfit', sans-serif;
            transition: all 0.2s;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-input:read-only {
            background: var(--bg);
            cursor: default;
        }

        .form-textarea {
            resize: vertical;
            min-height: 140px;
            line-height: 1.6;
        }

        .form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748B' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
        }

        .form-hint {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 8px;
        }

        /* LOCATION BUTTONS */
        .location-actions {
            display: flex;
            gap: 12px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .btn-location {
            padding: 11px 18px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-location:hover {
            background: var(--bg);
            border-color: var(--accent);
            color: var(--accent);
        }

        /* MAP */
        #pickerMap {
            height: 380px;
            border-radius: 12px;
            margin-top: 16px;
            border: 1px solid var(--border);
            display: none;
            overflow: hidden;
        }

        /* --- FIXED FILE UPLOAD CSS --- */
        .file-upload-box {
            display: block; /* Ensures it acts as a block container */
            width: 100%;    /* Ensures it takes full width */
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: var(--bg);
            position: relative;
        }

        .file-upload-box:hover {
            border-color: var(--accent);
            background: rgba(59, 130, 246, 0.03);
        }

        .file-upload-box input {
            display: none;
        }

        .file-upload-icon {
            font-size: 3rem;
            color: var(--text-muted);
            margin-bottom: 16px;
        }

        .file-upload-text {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .file-upload-subtext {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 16px;
        }

        .file-info-display {
            margin-top: 16px;
            padding: 12px 16px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.9rem;
            color: var(--text-muted);
            display: none;
        }

        .file-info-display.active {
            display: block;
        }

        /* BUTTONS */
        .btn {
            padding: 16px 28px;
            border-radius: 12px;
            border: none;
            background: var(--primary);
            color: white;
            font-weight: 700;
            font-size: 1.05rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
            width: 100%;
            justify-content: center;
        }

        .btn:hover {
            background: #1E293B;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }

        .btn:active {
            transform: translateY(0);
        }

        .footer { 
            text-align: center; 
            padding: 30px; 
            color: var(--text-muted); 
            font-size: 0.85rem; 
            border-top: 1px solid var(--border); 
            margin-top: auto; 
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .container {
                padding: 20px 16px;
            }

            .header {
                padding: 0 20px;
            }

            .card-body {
                padding: 24px 20px;
            }

            .location-actions {
                flex-direction: column;
            }

            .btn-location {
                width: 100%;
                justify-content: center;
            }

            .card-head {
                font-size: 1.15rem;
            }
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="brand">
            <i class="fa-solid fa-network-wired"></i> DhakaGrid
        </div>
        <a href="user_dashboard.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </header>

    <div class="container">

        <div class="card">
            <div class="card-head">
                <?php if ($is_banned): ?>
                    <i class="fa-solid fa-ban"></i> Account Restricted
                <?php else: ?>
                    <i class="fa-solid fa-paper-plane"></i> Submit New Problem
                <?php endif; ?>
            </div>
            
            <div class="card-body">
                
                <?php if ($is_banned): ?>
                    <div class="ban-alert">
                        <div class="ban-icon">
                            <i class="fa-solid fa-ban"></i>
                        </div>
                        <div class="ban-message">
                            <?= $ban_message ?>
                        </div>
                        <?php if (!empty($ban_reason)): ?>
                            <div class="ban-reason">
                                <strong><i class="fa-solid fa-info-circle"></i> Reason:</strong><br>
                                <?= htmlspecialchars($ban_reason) ?>
                            </div>
                        <?php endif; ?>
                        <p style="color: var(--text-muted); margin-top: 20px; font-size: 0.95rem;">
                            If you believe this is a mistake, please contact support.
                        </p>
                    </div>

                <?php else: ?>
                    <form method="POST" action="process_submit_problem.php" enctype="multipart/form-data">
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fa-solid fa-layer-group"></i>
                                Problem Category
                            </label>
                            <select name="category" class="form-select" required>
                                <option value="">Select a category</option>
                                <option value="police">Police</option>
                                <option value="medical">Medical</option>
                                <option value="fire">Fire</option>
                                <option value="gov">Government</option>
                                <option value="other">Other</option>
                            </select>
                            <div class="form-hint">Choose the category that best describes your issue</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fa-solid fa-location-dot"></i>
                                Location
                            </label>
                            <input 
                                type="text" 
                                name="location_name" 
                                id="locationName" 
                                class="form-input" 
                                placeholder="Click button below to set location" 
                                readonly 
                                required
                            >
                            
                            <div class="location-actions">
                                <button type="button" class="btn-location" id="useCurrent">
                                    <i class="fa-solid fa-location-crosshairs"></i> Use Current Location
                                </button>
                                <button type="button" class="btn-location" id="pickLocation">
                                    <i class="fa-solid fa-map-marked-alt"></i> Pick on Map
                                </button>
                            </div>

                            <div id="pickerMap"></div>
                            <div class="form-hint">We need your location to route this to the correct department</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fa-solid fa-align-left"></i>
                                Problem Description
                            </label>
                            <textarea 
                                name="description" 
                                class="form-textarea" 
                                placeholder="Describe the issue in detail...&#10;&#10;Example: The streetlight near House #12 has been broken for 3 days causing safety concerns at night."
                                required
                            ></textarea>
                            <div class="form-hint">Please provide as much detail as possible to help resolve the issue faster</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fa-solid fa-lightbulb"></i>
                                Suggestion (Optional)
                            </label>
                            <input 
                                type="text" 
                                name="suggestion" 
                                class="form-input" 
                                placeholder="Any suggestions for solving this issue?"
                            >
                            <div class="form-hint">Your suggestions can help authorities resolve issues more effectively</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fa-solid fa-image"></i>
                                Supporting Media (Optional)
                            </label>
                            
                            <label for="mediaInput" class="file-upload-box">
                                <div class="file-upload-icon">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <div class="file-upload-text">Upload Images or Videos</div>
                                <div class="file-upload-subtext">Click to browse or drag and drop files here</div>
                                <input 
                                    type="file" 
                                    name="media_files[]" 
                                    multiple 
                                    accept="image/*,video/*" 
                                    id="mediaInput"
                                >
                            </label>
                            
                            <div class="file-info-display" id="fileInfoDisplay">
                                <i class="fa-solid fa-check-circle" style="color: var(--success);"></i>
                                <span id="fileInfo">No files selected</span>
                            </div>
                            
                            <div class="form-hint">Supported formats: JPG, PNG, MP4, MOV (Max 10MB per file)</div>
                        </div>

                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">

                        <button type="submit" class="btn">
                            <i class="fa-solid fa-paper-plane"></i> Submit Report
                        </button>

                    </form>
                <?php endif; ?>

            </div>
        </div>

    </div>

    <footer class="footer">© 2026 Dhaka Grid Control. Connecting citizens.</footer>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Dhaka bounds for map restriction
        const dhakaBounds = L.latLngBounds([23.65, 90.30], [23.90, 90.55]);

        let pickerMap, pickerMarker;
        const pickerMapDiv = document.getElementById("pickerMap");
        const pickBtn = document.getElementById("pickLocation");
        const locName = document.getElementById("locationName");
        const latInput = document.getElementById("latitude");
        const lonInput = document.getElementById("longitude");

        // Use Current Location
        document.getElementById("useCurrent")?.addEventListener("click", () => {
            if (!navigator.geolocation) {
                alert("Geolocation is not supported by your browser");
                return;
            }

            navigator.geolocation.getCurrentPosition(
                async position => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    latInput.value = lat;
                    lonInput.value = lon;
                    
                    // Reverse geocode
                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`);
                        const data = await response.json();
                        locName.value = data.display_name || `${lat}, ${lon}`;
                    } catch (error) {
                        locName.value = `${lat}, ${lon}`;
                    }
                },
                error => {
                    alert("Unable to get your location. Please enable location services.");
                }
            );
        });

        // Pick Location on Map
        pickBtn?.addEventListener("click", () => {
            if (pickerMapDiv.style.display === "none" || pickerMapDiv.style.display === "") {
                pickerMapDiv.style.display = "block";
                
                if (!pickerMap) {
                    // Initialize map
                    pickerMap = L.map("pickerMap", {
                        center: [23.78, 90.40],
                        zoom: 12,
                        minZoom: 11,
                        maxZoom: 16,
                        maxBounds: dhakaBounds,
                        maxBoundsViscosity: 1.0
                    });
                    
                    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                        attribution: "&copy; OpenStreetMap contributors"
                    }).addTo(pickerMap);
                    
                    // Click to place marker
                    pickerMap.on("click", async e => {
                        const lat = e.latlng.lat;
                        const lon = e.latlng.lng;
                        
                        // Remove old marker
                        if (pickerMarker) {
                            pickerMap.removeLayer(pickerMarker);
                        }
                        
                        // Add new marker
                        pickerMarker = L.marker([lat, lon]).addTo(pickerMap);
                        
                        // Update form
                        latInput.value = lat;
                        lonInput.value = lon;
                        
                        // Reverse geocode
                        try {
                            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`);
                            const data = await response.json();
                            locName.value = data.display_name || `${lat}, ${lon}`;
                        } catch (error) {
                            locName.value = `${lat}, ${lon}`;
                        }
                    });
                } else {
                    setTimeout(() => pickerMap.invalidateSize(), 100);
                }
            } else {
                pickerMapDiv.style.display = "none";
            }
        });

        // File Upload Preview
        const mediaInput = document.getElementById("mediaInput");
        const fileInfo = document.getElementById("fileInfo");
        const fileInfoDisplay = document.getElementById("fileInfoDisplay");
        
        mediaInput?.addEventListener("change", () => {
            const files = mediaInput.files;
            
            if (files.length === 0) {
                fileInfo.textContent = "No files selected";
                fileInfoDisplay.classList.remove("active");
            } else if (files.length === 1) {
                fileInfo.textContent = `Selected: ${files[0].name}`;
                fileInfoDisplay.classList.add("active");
            } else {
                fileInfo.textContent = `${files.length} files selected`;
                fileInfoDisplay.classList.add("active");
            }
        });
    </script>
</body>
</html>