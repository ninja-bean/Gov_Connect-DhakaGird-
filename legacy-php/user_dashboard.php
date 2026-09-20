<?php
session_start();
require_once "db_connect.php";

// --- Authentication ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'Citizen';

// --- HANDLE SOS SUBMISSION (REAL GPS) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sos') {
    header('Content-Type: application/json');
    try {
        $lat = floatval($_POST['lat'] ?? 0);
        $lng = floatval($_POST['lng'] ?? 0);
        
        if(empty($lat) || empty($lng)) {
            throw new Exception("GPS location required.");
        }

        $stmt = $pdo->prepare("INSERT INTO problems (user_id, category, description, latitude, longitude, status, priority, created_at) VALUES (?, 'SOS', 'EMERGENCY SOS ALERT - Immediate assistance required', ?, ?, 'pending', 'high', NOW())");
        $stmt->execute([$user_id, $lat, $lng]);
        
        // Log in logs table if exists
        try {
            $logStmt = $pdo->prepare("INSERT INTO logs (problem_id, user_id, notification_type, message, created_at) VALUES (LAST_INSERT_ID(), ?, 'SOS', 'Emergency SOS alert triggered', NOW())");
            $logStmt->execute([$user_id]);
        } catch(Exception $e) {}
        
        echo json_encode(['status' => 'success']);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// --- FETCH PROFILE PIC ---
$user_pic = null;
$has_pic = false;
try {
    $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $uData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($uData['profile_pic']) && file_exists($uData['profile_pic'])) {
        $user_pic = $uData['profile_pic'];
        $has_pic = true;
    }
} catch (Exception $e) { }

// --- FETCH REPORTS & STATS ---
$reports = [];
$stats = ['total' => 0, 'sos' => 0, 'resolved' => 0];
$chartData = [0, 0, 0, 0, 0]; 
$total_xp = 0;

try {
    // Recent 5 reports
    $reportsStmt = $pdo->prepare("SELECT * FROM problems WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5");
    $reportsStmt->execute(['user_id' => $user_id]);
    $reports = $reportsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate Stats from ALL reports
    $fullStmt = $pdo->prepare("SELECT category, status FROM problems WHERE user_id = ?");
    $fullStmt->execute([$user_id]);
    $all = $fullStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($all as $r) {
        $stats['total']++;
        $cat = strtolower($r['category']);
        $status = strtolower($r['status']);

        // Count SOS
        if ($cat === 'sos') {
            $stats['sos']++;
        }
        
        // Count Resolved
        if ($status === 'resolved') {
            $stats['resolved']++;
            $total_xp += 50; 
        } else {
            $total_xp += 10;
        }
        
        // Chart Data - using lowercase for comparison
        if($cat === 'traffic') {
            $chartData[0]++;
        } elseif($cat === 'water') {
            $chartData[1]++;
        } elseif($cat === 'waste') {
            $chartData[2]++;
        } elseif($cat === 'sos') {
            $chartData[3]++;
        } else {
            $chartData[4]++;
        }
    }
} catch (Exception $e) { 
    error_log("Error fetching reports: " . $e->getMessage());
}

// --- FETCH MAP DATA (Real DB Data - Active problems only) ---
$mapReports = [];
try {
    $mapStmt = $pdo->prepare("
        SELECT latitude, longitude, category, status, created_at 
        FROM problems 
        WHERE latitude IS NOT NULL 
        AND longitude IS NOT NULL
        AND status NOT IN ('resolved', 'rejected')
        ORDER BY created_at DESC
        LIMIT 100
    ");
    $mapStmt->execute();
    $mapReports = $mapStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { 
    error_log("Error fetching map data: " . $e->getMessage());
}

// --- REAL TICKER DATA FROM DATABASE ---
$ticker_news = [];
try {
    // Get recently resolved issues
    $newsStmt = $pdo->prepare("
        SELECT category, location_name, updated_at 
        FROM problems 
        WHERE status = 'resolved' 
        ORDER BY updated_at DESC 
        LIMIT 8
    ");
    $newsStmt->execute();
    $resolved = $newsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($resolved as $item) {
        $location = !empty($item['location_name']) ? $item['location_name'] : 'Dhaka area';
        $ticker_news[] = "✅ Solved: " . ucfirst($item['category']) . " issue in " . $location;
    }
    
    // Add citywide stats
    $pendingStmt = $pdo->query("SELECT COUNT(*) as cnt FROM problems WHERE status = 'pending'");
    $pendingCount = $pendingStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    $ticker_news[] = "⚡ " . $pendingCount . " reports pending verification citywide";
    
    // Add active SOS alerts
    $sosStmt = $pdo->query("SELECT COUNT(*) as cnt FROM problems WHERE category = 'SOS' AND status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $activeSOS = $sosStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    if ($activeSOS > 0) {
        $ticker_news[] = "🚨 " . $activeSOS . " active emergency alerts in last 24 hours";
    }
    
} catch(Exception $e) {
    error_log("Error fetching news: " . $e->getMessage());
}

// Fallback if no data
if(empty($ticker_news)) {
    $ticker_news = [
        "⚡ Grid system operating normally.", 
        "📢 Report any issues immediately.", 
        "🌧️ Check weather updates before travel."
    ];
}

// --- FETCH GOVERNMENT NOTICES FROM DATABASE ---
$gov_notice = null;
try {
    $noticeStmt = $pdo->prepare("
        SELECT message, created_at 
        FROM warnings 
        WHERE user_id IS NULL OR user_id = ?
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $noticeStmt->execute([$user_id]);
    $gov_notice = $noticeStmt->fetch(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    // Warnings table doesn't exist - use default
}

// Default notice if none in DB
if (!$gov_notice) {
    $gov_notice = [
        'message' => 'Regular grid maintenance is scheduled for Sector 12 today. Please report any unexpected outages immediately.',
        'created_at' => date('Y-m-d H:i:s')
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dhaka Grid Control</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            display: flex; flex-direction: column;
        }

        /* --- GAMIFIED USER CARD CSS --- */
        .game-card { padding: 25px; background: #ffffff; border: 1px solid #E2E8F0; position: relative; }
        .gc-header { display: flex; align-items: center; gap: 20px; margin-bottom: 25px; }
        .gc-avatar-container { position: relative; width: 65px; height: 65px; }
        .gc-avatar {
            width: 100%; height: 100%; border-radius: 18px;
            background: linear-gradient(135deg, #0F172A 0%, #334155 100%);
            color: white; display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; box-shadow: 0 8px 20px -5px rgba(15, 23, 42, 0.3); overflow: hidden;
        }
        .gc-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .gc-level-badge {
            position: absolute; bottom: -8px; right: -8px;
            background: #EF4444; color: white; width: 32px; height: 32px;
            border-radius: 50%; border: 3px solid #ffffff;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            font-size: 0.55rem; line-height: 0.9; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .gc-info { display: flex; flex-direction: column; }
        .gc-name { font-size: 1.25rem; font-weight: 800; color: #0F172A; margin-bottom: 4px; }
        .gc-rank { 
            display: inline-flex; align-items: center; gap: 6px; 
            font-size: 0.8rem; font-weight: 700; color: #64748B; 
            background: #F1F5F9; padding: 4px 10px; border-radius: 20px; width: fit-content;
        }
        .gc-rank i { color: #3B82F6; }
        .gc-progress-box { margin-bottom: 25px; }
        .gc-progress-text { display: flex; justify-content: space-between; font-size: 0.75rem; font-weight: 700; color: #64748B; margin-bottom: 8px; }
        .gc-progress-track { width: 100%; height: 10px; background: #E2E8F0; border-radius: 20px; overflow: hidden; position: relative; }
        .gc-progress-fill { height: 100%; background: linear-gradient(90deg, #3B82F6 0%, #6366F1 100%); border-radius: 20px; position: relative; transition: width 0.5s ease-out; }
        .gc-shine {
            position: absolute; top: 0; left: 0; bottom: 0; width: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transform: skewX(-20deg) translateX(-150%);
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer { 100% { transform: skewX(-20deg) translateX(150%); } }
        .gc-next-rank { font-size: 0.7rem; color: #94A3B8; margin-top: 6px; text-align: right; }
        .gc-stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; border-top: 1px dashed #E2E8F0; padding-top: 20px; }
        .gc-stat { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 600; color: #475569; }

        /* --- WEATHER & NOTICE CARD CSS --- */
        .weather-card-ss {
            background: #1E1E1E; color: white; border-radius: 16px; 
            padding: 20px 25px; display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .wc-temp { font-size: 2.5rem; font-weight: 800; line-height: 1; margin-bottom: 8px; }
        .wc-loc { font-size: 0.9rem; color: #9CA3AF; font-weight: 500; }
        .wc-time { font-size: 0.85rem; color: #6B7280; margin-top: 3px; }
        .wc-icon { font-size: 2.8rem; color: #F8FAFC; opacity: 0.9; }

        .notice-card {
            background: #FEF3C7;
            border-left: 5px solid #F59E0B;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            color: #78350F;
        }
        .notice-title { font-weight: 800; font-size: 1rem; display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .notice-body { font-size: 0.9rem; line-height: 1.5; }

        /* --- STANDARD CSS --- */
        .header { background: var(--surface); height: 80px; padding: 0 40px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 1000; }
        .brand { font-size: 1.4rem; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        .nav-actions { display: flex; gap: 25px; align-items: center; }
        .nav-link { text-decoration: none; color: var(--text-main); font-weight: 600; font-size: 0.95rem; }
        .container { max-width: 1400px; margin: 30px auto; padding: 0 20px; width: 100%; flex: 1; }
        .grid-stats { display: grid; grid-template-columns: repeat(3, 1fr) 1.2fr; gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--surface); padding: 25px; border-radius: 16px; border: 1px solid var(--border); display: flex; flex-direction: column; justify-content: center; }
        .stat-val { font-size: 2.2rem; font-weight: 800; margin-bottom: 5px; color: var(--primary); }
        .stat-lbl { font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; }
        .actions-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .act-btn { padding: 20px; border: none; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none; transition: all 0.2s; }
        .btn-rep { background: var(--surface); border: 2px solid var(--primary); color: var(--primary); }
        .btn-rep:hover { background: var(--primary); color: white; }
        .btn-sos { background: var(--danger); color: white; animation: glow-red 3s infinite; }
        .grid-main { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        .card { background: var(--surface); border-radius: 16px; border: 1px solid var(--border); overflow: hidden; margin-bottom: 25px; }
        .card-head { padding: 18px 25px; border-bottom: 1px solid var(--border); font-weight: 700; display: flex; justify-content: space-between; align-items: center; }
        #map { height: 450px; width: 100%; z-index: 1; }
        .table-row { padding: 15px 25px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .b-sos { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; }
        .b-reg { background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; }
        .footer { text-align: center; padding: 40px; color: var(--text-muted); font-size: 0.85rem; border-top: 1px solid var(--border); margin-top: auto; }
        
        /* TICKER */
        .news-bar { background: linear-gradient(90deg, #0F172A 0%, #1E293B 100%); color: white; border-radius: 16px; padding: 25px 35px; margin-bottom: 35px; display: flex; align-items: center; gap: 30px; }
        .news-wrapper { flex: 1; height: 30px; position: relative; overflow: hidden; }
        .news-item { position: absolute; width: 100%; font-size: 1.25rem; font-weight: 500; opacity: 0; transform: translateY(30px); transition: all 0.6s; }
        .news-item.active { opacity: 1; transform: translateY(0); }
        .news-item.exit { opacity: 0; transform: translateY(-30px); }

        /* MODAL */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
        .modal-content { background: white; padding: 40px; border-radius: 20px; width: 90%; max-width: 400px; text-align: center; }
        @keyframes glow-red { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 50% { box-shadow: 0 0 15px 0 rgba(239, 68, 68, 0.2); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } }
        @media (max-width: 1000px) { .grid-stats { grid-template-columns: 1fr 1fr; } .grid-main { grid-template-columns: 1fr; } }
    </style>
</head>
<body>


<div id="successModal" class="modal" style="<?= isset($_GET['submitted']) ? 'display:flex;' : '' ?>">
    <div class="modal-content">
        <div class="success-icon-wrapper" style="margin-bottom: 20px;">
            <i class="fa-solid fa-circle-check fa-4x" style="color: var(--success); animation: pulse-green 2s infinite;"></i>
        </div>
        <h2>Report Received!</h2>
        <p style="color: var(--text-muted); margin-bottom: 25px;">
            Your report has been logged into the grid. Our response teams have been notified.
        </p>
        <div style="background: #F1F5F9; padding: 15px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-award" style="color: #F59E0B;"></i>
            <span style="font-size: 0.9rem; font-weight: 600; color: #475569;">+10 XP Earned for City Watch</span>
        </div>
        <button onclick="closeSuccess()" style="width: 100%; padding: 15px; border: none; background: var(--primary); color: white; border-radius: 12px; font-weight: 700; cursor: pointer;">
            Back to Dashboard
        </button>
    </div>
</div>

<style>
@keyframes pulse-green {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}
</style>

    <header class="header">
        <div style="display: flex; align-items: center;">
            <div class="brand"><i class="fa-solid fa-network-wired"></i> DhakaGrid</div>
        </div>
        <div class="nav-actions">
            <a href="profile.php" class="nav-link">Profile</a>
            <a href="logout.php" class="nav-link logout">Logout</a>
        </div>
    </header>

    <div class="container">

        <div class="news-bar">
            <div style="background:rgba(255,255,255,0.1); padding:5px 15px; border-radius:20px; font-weight:800; font-size:0.8rem; color:#EF4444;">LIVE FEED</div>
            <div class="news-wrapper" id="newsWrapper"></div>
        </div>

        <div class="grid-stats">
            <div class="stat-card">
                <div class="stat-val"><?= $stats['total'] ?></div>
                <div class="stat-lbl">My Reports</div>
            </div>
            <div class="stat-card">
                <div class="stat-val" style="color: var(--danger);"><?= $stats['sos'] ?></div>
                <div class="stat-lbl">SOS Alerts</div>
            </div>
            <div class="stat-card">
                <div class="stat-val" style="color: var(--success);"><?= $stats['resolved'] ?></div>
                <div class="stat-lbl">Resolved</div>
            </div>
            
            <div class="weather-card-ss">
                <div>
                    <div class="wc-temp" id="w-temp">--°</div>
                    <div class="wc-loc">Dhaka</div>
                    <div class="wc-time" id="w-desc">Loading...</div>
                </div>
                <div class="wc-icon" id="w-icon">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                </div>
            </div>      
        </div>

        <div class="actions-row">
            <a href="submit_problem.php" class="act-btn btn-rep">
                <i class="fa-solid fa-pen-to-square"></i> Submit Report
            </a>
            <button onclick="openSOS()" class="act-btn btn-sos">
                <i class="fa-solid fa-bullhorn"></i> SEND SOS
            </button>
        </div>

        <div class="grid-main">
            <div>
                <div class="card">
                    <div class="card-head">
                        <span><i class="fa-regular fa-map"></i> Live Grid Map</span>
                        <span style="font-size: 0.8rem; color: var(--success);">● Online</span>
                    </div>
                    <div id="map"></div>
                </div>

                <div class="card">
                    <div class="card-head">Recent Activity</div>
                    <div>
                        <?php if (empty($reports)): ?>
                            <div style="padding: 25px; text-align: center; color: var(--text-muted);">No recent reports.</div>
                        <?php else: ?>
                            <?php foreach ($reports as $r): 
                                $isSOS = (strtoupper($r['category']) == 'SOS');
                            ?>
                            <div class="table-row" style="<?= $isSOS ? 'background: #FEF2F2;' : '' ?>">
                                <div>
                                    <div style="font-weight: 700; margin-bottom: 2px;">
                                        <?= $isSOS ? '🚨 SOS ALERT' : ucfirst($r['category']) ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                                        <?= date('M d, h:i A', strtotime($r['created_at'])) ?>
                                    </div>
                                </div>
                                <span class="badge <?= $isSOS ? 'b-sos' : 'b-reg' ?>"><?= $r['status'] ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div>
                <div class="notice-card">
                    <div class="notice-title">
                        <i class="fa-solid fa-bullhorn"></i> GOV NOTICE
                    </div>
                    <div class="notice-body">
                        <strong>Official Broadcast:</strong>
                        <br>
                        <?= htmlspecialchars($gov_notice['message']) ?>
                        <br><br>
                        <span style="font-size:0.75rem; opacity:0.8;">Issued: <?= date('F j, Y', strtotime($gov_notice['created_at'])) ?></span>
                    </div>
                </div>

                <div class="card game-card">
                    <div class="gc-header">
                        <div class="gc-avatar-container">
                            <div class="gc-avatar">
                                <?php if ($has_pic): ?>
                                    <img src="<?= htmlspecialchars($user_pic) ?>?v=<?= time() ?>" alt="User">
                                <?php else: ?>
                                    <i class="fa-solid fa-user-astronaut"></i>
                                <?php endif; ?>
                            </div>
                            <div class="gc-level-badge">
                                <span>LVL</span>
                                <b><?= floor($total_xp / 100) + 1 ?></b>
                            </div>
                        </div>
                        <div class="gc-info">
                            <h4 class="gc-name"><?= htmlspecialchars($user_name) ?></h4>
                            <div class="gc-rank">
                                <i class="fa-solid fa-shield-halved"></i> 
                                <span>City Watcher</span>
                            </div>
                        </div>
                    </div>

                    <div class="gc-progress-box">
                        <div class="gc-progress-text">
                            <span>Current XP</span>
                            <span><?= $total_xp ?> / 1000</span>
                        </div>
                        <div class="gc-progress-track">
                            <div class="gc-progress-fill" style="width: <?= min(100, $total_xp/10) ?>%;">
                                <div class="gc-shine"></div>
                            </div>
                        </div>
                        <div class="gc-next-rank">Next Rank: <b>Grid Guardian</b></div>
                    </div>

                    <div class="gc-stats-row">
                        <div class="gc-stat">
                            <i class="fa-solid fa-clipboard-check" style="color: #3B82F6;"></i>
                            <span><?= $stats['total'] ?> Reports</span>
                        </div>
                        <div class="gc-stat">
                            <i class="fa-solid fa-star" style="color: #F59E0B;"></i>
                            <span><?= $stats['resolved'] ?> Solved</span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">Report Analytics</div>
                    <div style="padding: 20px;">
                        <canvas id="myChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">© 2026 Dhaka Grid Control. Connecting citizens.Made with ❤️ by Injabin</footer>

    <div id="sosModal" class="modal">
        <div class="modal-content">
            <div id="step1">
                <i class="fa-solid fa-triangle-exclamation fa-3x" style="color: var(--danger); margin-bottom: 20px;"></i>
                <h2>Emergency SOS</h2>
                <p style="color: var(--text-muted); margin-bottom: 25px;">
                    This will broadcast your <strong>exact GPS location</strong>.
                </p>
                <div style="display: flex; gap: 10px;">
                    <button onclick="closeSOS()" style="flex:1; padding: 12px; border: 1px solid #ddd; background: white; border-radius: 8px; cursor: pointer;">Cancel</button>
                    <button onclick="initSOS()" style="flex:1; padding: 12px; border: none; background: var(--danger); color: white; border-radius: 8px; font-weight: 700; cursor: pointer;">CONFIRM</button>
                </div>
            </div>
            <div id="step2" style="display: none;">
                <i class="fa-solid fa-satellite-dish fa-spin fa-2x" style="color: var(--accent);"></i>
                <p style="margin-top: 15px;">Acquiring GPS Satellites...</p>
            </div>
            <div id="step3" style="display: none;">
                <i class="fa-solid fa-check-circle fa-3x" style="color: var(--success); margin-bottom: 15px;"></i>
                <h3>Location Sent!</h3>
                <button onclick="location.reload()" style="margin-top: 20px; padding: 10px 30px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer;">OK</button>
            </div>
            <div id="stepError" style="display: none;">
                 <i class="fa-solid fa-triangle-exclamation fa-2x" style="color: var(--danger);"></i>
                 <p id="errorMsg" style="margin-top:10px;">Location failed.</p>
                 <button onclick="closeSOS()" style="margin-top: 15px; padding: 8px 20px; border:1px solid #ccc; background:white; border-radius:4px;">Close</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>

        
        // --- REAL WEATHER API (Open-Meteo - Free, No Key) ---
        function fetchWeather() {
            fetch('https://api.open-meteo.com/v1/forecast?latitude=23.8103&longitude=90.4125&current=temperature_2m,is_day,weather_code&timezone=auto')
            .then(response => response.json())
            .then(data => {
                const temp = Math.round(data.current.temperature_2m);
                const code = data.current.weather_code;
                const isDay = data.current.is_day;
                
                document.getElementById('w-temp').innerText = temp + "°C";
                document.getElementById('w-desc').innerText = "Dhaka, BD";
                
                // Map WMO Weather Code to Icon
                let iconClass = "fa-cloud";
                if(code === 0) iconClass = isDay ? "fa-sun" : "fa-moon";
                else if(code <= 3) iconClass = isDay ? "fa-cloud-sun" : "fa-cloud-moon";
                else if(code >= 51 && code <= 67) iconClass = "fa-cloud-rain";
                else if(code >= 80) iconClass = "fa-cloud-showers-heavy";
                
                document.getElementById('w-icon').innerHTML = `<i class="fa-solid ${iconClass}"></i>`;
            })
            .catch(err => {
                document.getElementById('w-temp').innerText = "--°C";
                document.getElementById('w-desc').innerText = "Weather unavailable";
                document.getElementById('w-icon').innerHTML = '<i class="fa-solid fa-exclamation-triangle"></i>';
            });
        }
        fetchWeather();

        // --- NEWS TICKER (Real DB Data) ---
        const newsData = <?= json_encode($ticker_news) ?>;
        const wrapper = document.getElementById('newsWrapper');
        let currentNewsIdx = 0;

        function showNextNews() {
            const oldItem = wrapper.querySelector('.news-item');
            if(oldItem) {
                oldItem.classList.remove('active');
                oldItem.classList.add('exit');
                setTimeout(() => oldItem.remove(), 600);
            }
            const div = document.createElement('div');
            div.className = 'news-item';
            div.innerText = newsData[currentNewsIdx];
            wrapper.appendChild(div);
            setTimeout(() => div.classList.add('active'), 50);
            currentNewsIdx = (currentNewsIdx + 1) % newsData.length;
        }
        setInterval(showNextNews, 4500);
        showNextNews();

        // --- MAP (Real DB Coordinates) ---
        const map = L.map('map', {
            center: [23.7806, 90.4193],
            zoom: 12,
            maxBounds: L.latLngBounds(L.latLng(23.65, 90.32), L.latLng(23.90, 90.52))
        });
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { attribution: '' }).addTo(map);

        <?php foreach($mapReports as $r): if(!empty($r['latitude']) && !empty($r['longitude'])): 
            $isSOS = (strtoupper($r['category']) === 'SOS');
        ?>
            L.circleMarker([<?= $r['latitude'] ?>, <?= $r['longitude'] ?>], {
                radius: 8,
                fillColor: "<?= $isSOS ? '#EF4444' : '#0F172A' ?>",
                color: "white", 
                weight: 2, 
                fillOpacity: 0.9
            }).bindPopup('<strong><?= htmlspecialchars($r['category']) ?></strong><br><small><?= date('M d, h:i A', strtotime($r['created_at'])) ?></small>').addTo(map);
        <?php endif; endforeach; ?>

        // --- CHART (Real Category Data) ---
        const ctx = document.getElementById('myChart').getContext('2d');
        const chartDataRaw = [<?= implode(',', $chartData) ?>];
        const finalChartData = chartDataRaw.every(item => item === 0) ? [1] : chartDataRaw;
        const finalColors = chartDataRaw.every(item => item === 0) ? ['#E2E8F0'] : ['#1E293B', '#3B82F6', '#10B981', '#EF4444', '#94A3B8'];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Traffic', 'Water', 'Waste', 'SOS', 'Other'],
                datasets: [{ data: finalChartData, backgroundColor: finalColors, borderWidth: 0 }]
            },
            options: { 
                cutout: '75%', 
                plugins: { 
                    legend: { 
                        position: 'bottom', 
                        labels: { 
                            usePointStyle: true, 
                            font: {size: 11} 
                        } 
                    } 
                } 
            }
        });

        // --- SOS & GPS (Real Location Capture) ---
        function openSOS() { 
            document.getElementById('sosModal').style.display = 'flex'; 
            document.getElementById('step1').style.display = 'block'; 
        }
        
        function closeSOS() { 
            document.getElementById('sosModal').style.display = 'none'; 
            document.getElementById('stepError').style.display = 'none'; 
        }
        
        function initSOS() {
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';

            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    function(position) { 
                        sendSOSData(position.coords.latitude, position.coords.longitude); 
                    },
                    function(error) { 
                        showError("GPS Access Denied. Enable location services."); 
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else { 
                showError("Browser does not support GPS."); 
            }
        }

        function sendSOSData(lat, lng) {
            const formData = new FormData();
            formData.append('action', 'sos');
            formData.append('lat', lat);
            formData.append('lng', lng);
            
            fetch('', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if(data.status === 'success') {
                    document.getElementById('step2').style.display = 'none';
                    document.getElementById('step3').style.display = 'block';
                } else { 
                    showError(data.message || 'Failed to send SOS alert'); 
                }
            })
            .catch(err => showError("Network Error. Please try again."));
        }

        function showError(msg) {
            document.getElementById('step2').style.display = 'none';
            document.getElementById('stepError').style.display = 'block';
            document.getElementById('errorMsg').innerText = msg;
        }
    </script>
</body>
</html>