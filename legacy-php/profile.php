<?php
session_start();
require_once "db_connect.php";

// --- Authentication ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// --- Fetch User Data ---
$stmt = $pdo->prepare("SELECT user_id, name, email, phone, location, latitude, longitude, profile_pic, nid, dob FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { 
    echo "User not found."; 
    exit; 
}

// --- Flash Messages ---
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - DhakaGrid</title>
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
        
        .nav-actions { 
            display: flex; 
            gap: 15px; 
            align-items: center; 
        }
        
        .nav-link { 
            text-decoration: none; 
            color: var(--text-main); 
            font-weight: 600; 
            font-size: 0.95rem;
            padding: 8px 16px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .nav-link:hover {
            background: rgba(0,0,0,0.05);
        }

        /* CONTAINER */
        .container { 
            max-width: 1200px; 
            margin: 30px auto; 
            padding: 0 40px; 
            width: 100%; 
            flex: 1; 
        }

        /* ALERTS */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
        }

        .alert-success {
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }

        .alert-error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
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
            font-size: 1.15rem;
            background: linear-gradient(180deg, #FFFFFF 0%, #FAFAFA 100%);
        }

        .card-body {
            padding: 28px;
        }

        /* PROFILE HEADER */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 30px;
            padding-bottom: 28px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 28px;
        }

        .profile-pic-container {
            position: relative;
        }

        .profile-pic {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--border);
            background: var(--bg);
        }

        .upload-overlay {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid white;
            transition: all 0.2s;
        }

        .upload-overlay:hover {
            transform: scale(1.1);
        }

        .upload-overlay input {
            display: none;
        }

        .profile-info h2 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .profile-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .meta-item i {
            color: var(--accent);
        }

        /* FORM */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-main);
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-input:disabled {
            background: var(--bg);
            color: var(--text-muted);
            cursor: not-allowed;
        }

        .form-hint {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 6px;
        }

        /* LOCATION SECTION */
        .location-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .btn-location {
            padding: 10px 16px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.85rem;
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

        #mapPicker {
            height: 360px;
            border-radius: 12px;
            margin-top: 16px;
            border: 1px solid var(--border);
            display: none;
            overflow: hidden;
        }

        /* BUTTONS */
        .btn {
            padding: 14px 24px;
            border-radius: 10px;
            border: none;
            background: var(--primary);
            color: white;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }

        .btn:hover {
            background: #1E293B;
        }

        .btn-secondary {
            background: var(--bg);
            color: var(--text-main);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--surface);
            border-color: var(--primary);
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
        @media (max-width: 1000px) { 
            .form-grid { 
                grid-template-columns: 1fr; 
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .container {
                padding: 0 20px;
            }

            .header {
                padding: 0 20px;
            }

            .nav-actions {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>

    <header class="header">
        <div style="display: flex; align-items: center;">
            <div class="brand"><i class="fa-solid fa-network-wired"></i> DhakaGrid</div>
        </div>
        <div class="nav-actions">
            <a href="user_dashboard.php" class="nav-link">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
            <a href="change_password.php" class="nav-link">
                <i class="fa-solid fa-key"></i> Change Password
            </a>
            <a href="logout.php" class="nav-link">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </header>

    <div class="container">

        <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check" style="font-size: 1.2rem;"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 1.2rem;"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-head">
                <i class="fa-solid fa-user-circle"></i> My Profile
            </div>
            
            <div class="card-body">
                <form id="profileForm" method="post" action="profile_controller.php" enctype="multipart/form-data">

                    <!-- Profile Header -->
                    <div class="profile-header">
                        <div class="profile-pic-container">
                            <?php
                                $picPath = (!empty($user['profile_pic']) && file_exists(__DIR__."/profile_pics/".$user['profile_pic']))
                                    ? "profile_pics/".htmlspecialchars($user['profile_pic'])
                                    : "https://ui-avatars.com/api/?name=".urlencode($user['name'])."&size=140&background=0F172A&color=fff&bold=true";
                            ?>
                            <img id="previewImg" src="<?= $picPath ?>" class="profile-pic" alt="Profile Picture">
                            <label class="upload-overlay">
                                <i class="fa-solid fa-camera"></i>
                                <input type="file" id="profilePicInput" name="profile_pic" accept="image/*">
                            </label>
                        </div>

                        <div class="profile-info">
                            <h2><?= htmlspecialchars($user['name']) ?></h2>
                            <div class="profile-meta">
                                <div class="meta-item">
                                    <i class="fa-solid fa-envelope"></i>
                                    <span><?= htmlspecialchars($user['email']) ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fa-solid fa-phone"></i>
                                    <span><?= htmlspecialchars($user['phone']) ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fa-solid fa-id-card"></i>
                                    <span>NID: <?= htmlspecialchars($user['nid']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Fields -->
                    <div class="form-grid">
                        
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input class="form-input" type="text" value="<?= htmlspecialchars($user['name']) ?>" disabled>
                            <div class="form-hint">Contact admin to change your name</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input class="form-input" type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            <div class="form-hint">Email cannot be changed</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input class="form-input" type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="Enter phone number">
                            <div class="form-hint">You can update your phone number</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">National ID (NID)</label>
                            <input class="form-input" type="text" value="<?= htmlspecialchars($user['nid']) ?>" disabled>
                            <div class="form-hint">NID is verified and cannot be changed</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Date of Birth</label>
                            <input class="form-input" type="date" value="<?= htmlspecialchars($user['dob']) ?>" disabled>
                            <div class="form-hint">DOB cannot be changed</div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Location</label>
                            <input class="form-input" type="text" id="locationText" name="location" value="<?= htmlspecialchars($user['location']) ?>" readonly placeholder="Click button below to set location">
                            <input type="hidden" id="latitude" name="latitude" value="<?= htmlspecialchars($user['latitude']) ?>">
                            <input type="hidden" id="longitude" name="longitude" value="<?= htmlspecialchars($user['longitude']) ?>">
                            
                            <div class="location-actions">
                                <button type="button" id="useCurrent" class="btn-location">
                                    <i class="fa-solid fa-location-crosshairs"></i> Use Current Location
                                </button>
                                <button type="button" id="pickOnMap" class="btn-location">
                                    <i class="fa-solid fa-map-marked-alt"></i> Pick on Map
                                </button>
                            </div>

                            <div id="mapPicker"></div>
                        </div>

                    </div>

                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 12px; margin-top: 24px;">
                        <button type="submit" name="update_profile" class="btn">
                            <i class="fa-solid fa-save"></i> Save Changes
                        </button>
                        <button type="button" onclick="window.location.href='user_dashboard.php'" class="btn btn-secondary">
                            <i class="fa-solid fa-times"></i> Cancel
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>

    <footer class="footer">© 2026 Dhaka Grid Control. Connecting citizens. Made with ❤️ by Injabin</footer>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Dhaka bounds for map restriction
        const dhakaBounds = L.latLngBounds([23.65, 90.30], [23.90, 90.55]);

        // Image Preview
        const fileInput = document.getElementById('profilePicInput');
        const previewImg = document.getElementById('previewImg');
        
        fileInput.addEventListener('change', e => {
            const file = e.target.files[0];
            if (!file) return;
            
            // Check file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Image is too large! Maximum size is 2MB.');
                fileInput.value = '';
                return;
            }
            
            // Check file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Invalid file type! Only JPG, PNG, and WebP are allowed.');
                fileInput.value = '';
                return;
            }
            
            // Preview image
            const reader = new FileReader();
            reader.onload = () => previewImg.src = reader.result;
            reader.readAsDataURL(file);
        });

        // Use Current Location
        document.getElementById('useCurrent').onclick = () => {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser');
                return;
            }
            
            navigator.geolocation.getCurrentPosition(
                async position => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;
                    
                    // Reverse geocode to get address
                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`);
                        const data = await response.json();
                        document.getElementById('locationText').value = data.display_name || `${lat}, ${lng}`;
                    } catch (error) {
                        document.getElementById('locationText').value = `${lat}, ${lng}`;
                    }
                },
                error => {
                    alert('Unable to get your location. Please enable location services.');
                }
            );
        };

        // Map Picker
        let pickerMap, pickerMarker;
        
        document.getElementById('pickOnMap').onclick = () => {
            const mapDiv = document.getElementById('mapPicker');
            
            if (mapDiv.style.display === 'block') {
                mapDiv.style.display = 'none';
                return;
            }
            
            mapDiv.style.display = 'block';
            
            if (!pickerMap) {
                // Initialize map
                pickerMap = L.map('mapPicker', {
                    center: [23.78, 90.40],
                    zoom: 12,
                    maxBounds: dhakaBounds,
                    maxBoundsViscosity: 1.0
                });
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(pickerMap);
                
                // Add existing marker if coordinates exist
                const existingLat = document.getElementById('latitude').value;
                const existingLng = document.getElementById('longitude').value;
                if (existingLat && existingLng) {
                    pickerMarker = L.marker([existingLat, existingLng]).addTo(pickerMap);
                    pickerMap.setView([existingLat, existingLng], 13);
                }
                
                // Click to place marker
                pickerMap.on('click', async e => {
                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;
                    
                    // Remove old marker
                    if (pickerMarker) {
                        pickerMap.removeLayer(pickerMarker);
                    }
                    
                    // Add new marker
                    pickerMarker = L.marker([lat, lng]).addTo(pickerMap);
                    
                    // Update form fields
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;
                    
                    // Reverse geocode
                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`);
                        const data = await response.json();
                        document.getElementById('locationText').value = data.display_name || `${lat}, ${lng}`;
                    } catch (error) {
                        document.getElementById('locationText').value = `${lat}, ${lng}`;
                    }
                });
            } else {
                // Map already exists, just resize it
                setTimeout(() => pickerMap.invalidateSize(), 150);
            }
        };
    </script>
</body>
</html>