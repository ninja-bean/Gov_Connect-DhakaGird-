<?php
session_start();
require_once "db_connect.php";

// ✅ Ensure response team login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'response') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


// ✅ Fetch available members for this response team
$teamStmt = $pdo->prepare("SELECT (total_members - busy_members) AS available_members FROM users WHERE user_id = ?");
$teamStmt->execute([$user_id]);
$teamData = $teamStmt->fetch(PDO::FETCH_ASSOC);
$available_members = $teamData ? (int)$teamData['available_members'] : 0;


// ✅ Fetch all problems assigned to this team, excluding deleted ones
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        u.name AS user_name,
        u.email AS user_email,
        u.phone AS user_phone
    FROM problems p
    JOIN users u ON p.user_id = u.user_id
    WHERE p.assigned_to = ? 
      AND p.problem_id NOT IN (SELECT problem_id FROM deleted_problems)
    ORDER BY p.created_at DESC
");
$stmt->execute([$user_id]);
$problems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$deletedProblems = $pdo->prepare("
    SELECT d.*, u.name AS user_name, u.email AS user_email
    FROM deleted_problems d
    LEFT JOIN users u ON d.user_id = u.user_id
    LEFT JOIN problems p ON d.problem_id = p.problem_id
    WHERE p.assigned_to = ?
    ORDER BY d.deleted_at DESC
");
$deletedProblems->execute([$user_id]);
$deletedProblems = $deletedProblems->fetchAll(PDO::FETCH_ASSOC);


// ✅ Group problems by priority
$grouped = [
    "sos" => [],
    "high" => [],
    "medium" => [],
    "low" => []
];
foreach ($problems as $p) {
    // Normalize text for detection
    $priority = strtolower(trim($p['priority']));
    $desc = strtolower($p['description'] ?? '');

    // SOS detection first (priority or description)
    if ($priority === 'sos' || str_contains($desc, 'sos')) {
        $grouped['sos'][] = $p;
    } elseif ($priority === 'high') {
        $grouped['high'][] = $p;
    } elseif ($priority === 'medium') {
        $grouped['medium'][] = $p;
    } else {
        $grouped['low'][] = $p;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Response Dashboard - GovConnect</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

<style>
:root {--bg1:#0f172a;--bg2:#1e293b;--accent1:#667eea;--accent2:#764ba2;--success:#27ae60;--danger:#e74c3c;--muted:#94a3b8;}
.light-theme {--bg1:#f5f3ff;--bg2:#ede9fe;--accent1:#a78bfa;--accent2:#c4b5fd;--success:#16a34a;--danger:#dc2626;color:#111;}
.light-theme body { color:#111; background:linear-gradient(135deg,var(--bg1),var(--bg2)); }
.light-theme .btn-ghost { color:#333; background:rgba(255,255,255,0.5); }
.light-theme .card,.light-theme .problem-card { background:rgba(255,255,255,0.6); color:#111; }
.light-theme .contact { background:rgba(255,255,255,0.8); color:#111; }

body { margin:0; font-family: 'Inter', sans-serif; background: linear-gradient(135deg,var(--bg1),var(--bg2)); color:#fff; }
.topbar { display:flex;justify-content:space-between;align-items:center;padding:14px 28px;background: rgba(255,255,255,0.05);backdrop-filter: blur(12px);border-bottom:1px solid rgba(255,255,255,0.08); }
.brand { display:flex;align-items:center;gap:10px;font-weight:700; }
.brand i { font-size:24px; }
.user-area { display:flex;align-items:center;gap:14px; }
.user-area span { font-weight:600; }
.btn-ghost { background: rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.12); padding:8px 14px; border-radius:10px; color:#fff; cursor:pointer; transition:all .25s; }
.btn-ghost:hover { background: rgba(255,255,255,0.18); }
.container { max-width:1100px; margin:30px auto; padding:0 18px; }
.card { background: rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); backdrop-filter: blur(14px); border-radius:14px; padding:18px; margin-bottom:20px; box-shadow:0 6px 25px rgba(0,0,0,0.4); }
.card h4 { margin-top:0;margin-bottom:12px; }
.problem-card { background: rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:14px; margin-bottom:15px; box-shadow:0 4px 12px rgba(0,0,0,0.3); }
.problem-card strong { font-size:20px; }
.problem-card p { margin:6px 0; }
.badge { padding:4px 10px; border-radius:999px; font-size:17px; font-weight:600; color:#fff; }
.status-pending{ background:#f39c12; }
.status-verified{ background:var(--success); }
.status-assigned{ background:#0ea5a1; }
.status-working{ background:#3498db; }
.status-resolved{ background:#2b7cff; }
.status-rejected{ background:var(--danger); }
.contact { margin-top:10px; padding:10px; border-radius:10px; background:rgba(255,255,255,0.05); font-size:17px; }
.form-inline { display:flex;flex-wrap:wrap;gap:10px;margin-top:12px; }
.input { flex:1; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.05); color:#fff; min-width:140px; }
select.input { background:#1e293b; color:#fff; border:1px solid #444; }
textarea.input { background:#1e293b; color:#fff; border:1px solid #444; min-height:60px; }
.btn-gradient { padding:10px 16px; border:none; border-radius:10px; background: linear-gradient(90deg,var(--accent1),var(--accent2)); color:#fff; font-weight:600; cursor:pointer; transition: all .25s; }
.btn-gradient:hover { transform:translateY(-2px); }
#map { height:420px; border-radius:12px; margin-bottom:20px; }
</style>
</head>
<body>

<header class="topbar">
  <div class="brand"><i class="fa-solid fa-shield-halved"></i> GovConnect</div>
  <div class="user-area">
    <button class="btn-ghost" id="theme-toggle"><i class="fa-solid fa-paint-brush"></i> Theme</button>
    <span>Welcome, <?= htmlspecialchars($_SESSION['name']); ?></span>
    <button class="btn-ghost" onclick="location.href='response_profile.php'"><i class="fa-solid fa-user"></i> Profile</button>
    <button class="btn-ghost" onclick="location.href='logout.php'"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
  </div>
</header>

<div class="container">

  <!-- Map -->
  <div class="card">
    <h4><i class="fa-solid fa-map-location-dot"></i> Assigned Problems Map</h4>
    <div id="map"></div>
  </div>

  <!-- Grouped Problems -->
  <?php foreach (['sos'=>'🚨 SOS Emergencies','high'=>'🔥 High Priority','medium'=>'⚠️ Medium Priority','low'=>'ℹ️ Low Priority'] as $key=>$label): ?>
    <div class="card">
      <h4><?= $label ?></h4>
      <?php if (count($grouped[$key])>0): foreach($grouped[$key] as $row): ?>
        <div class="problem-card">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div>
              <?php if ($row['status'] === 'working' && (int)$row['working_members'] > 0): ?>
                <div style="text-align:right;margin-top:-10px;margin-bottom:8px;">
                  <span style="background:#3498db;color:#fff;padding:4px 10px;border-radius:8px;font-size:14px;">
                    👷 <?= (int)$row['working_members'] ?> members working
                  </span>
                </div>
              <?php endif; ?>
              <strong>#<?= (int)$row['problem_id'] ?> — <?= ucfirst(htmlspecialchars($row['category'])) ?></strong>
              <br><small class="muted"><?= htmlspecialchars($row['created_at'] ?? '') ?></small>
            </div>
            <div>
              <span class="badge status-<?= strtolower($row['status'] ?? 'pending') ?>">
                <?= ucfirst($row['status'] ?? 'Pending') ?>
              </span>
            </div>
          </div>

          <p><?= nl2br(htmlspecialchars($row['description'] ?? '')) ?></p>
          <?php if(!empty($row['suggestion'])): ?>
            <div class="contact"><b>Suggestion:</b> <?= htmlspecialchars($row['suggestion']) ?></div>
          <?php endif; ?>
          <div class="contact"><b>Location:</b> <?= htmlspecialchars($row['location_name'] ?: $row['location']) ?></div>

          <!-- User info -->
          <div class="contact">
            <i class="fa fa-user"></i> <?= htmlspecialchars($row['user_name']) ?><br>
            <i class="fa fa-envelope"></i> <?= htmlspecialchars($row['user_email']) ?><br>
            <i class="fa fa-phone"></i> <?= htmlspecialchars($row['user_phone']) ?>
          </div>

          <!-- Update & Delete form -->
          <form class="form-inline" method="POST" action="update_problem_status.php">
            <input type="hidden" name="problem_id" value="<?= (int)$row['problem_id'] ?>">
            <?php if ($row['status'] !== 'resolved'): ?>
              <?php if ($row['status'] !== 'working'): ?>
<input class="input" type="number" name="members" min="1" max="<?= $available_members ?>" placeholder="Team Members (<?= $available_members ?> available)" required>
                <select class="input" name="status_update" required>
                  <option value="">-- Select Status --</option>
                  <option value="working">Working</option>
                </select>
                <button class="btn-gradient" type="submit" name="action" value="update"><i class="fa fa-sync-alt"></i> Update</button>
              <?php else: ?>
                <textarea class="input" name="report" placeholder="Enter report here..." required></textarea>
                <input type="hidden" name="status_update" value="resolved">
                <input type="hidden" name="members" value="<?= (int)$row['working_members'] ?>">
                <button class="btn-gradient" type="submit" name="action" value="update"><i class="fa fa-check"></i> Resolve</button>
              <?php endif; ?>
            <?php endif; ?>
            
          </form>

        </div>
      <?php endforeach; else: ?>
        <p style="color:var(--muted)">No <?= strtolower($label) ?> problems assigned.</p>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <!-- Deleted Complaints Section with Toggle -->
  <div class="card">
    <h4>🗑 Deleted Complaints</h4>
    <button class="btn-gradient" id="toggle-deleted"><i class="fa fa-eye"></i> Show Deleted Complaints</button>
    <div id="deleted-table" style="display:none;margin-top:15px;overflow-x:auto;">
      <?php if(count($deletedProblems)>0): ?>
        <table style="width:100%;border-collapse:collapse;">
          <thead>
            <tr style="background:rgba(255,255,255,0.1);">
              <th style="padding:8px;border:1px solid rgba(255,255,255,0.1);">ID</th>
              <th style="padding:8px;border:1px solid rgba(255,255,255,0.1);">Category</th>
              <th style="padding:8px;border:1px solid rgba(255,255,255,0.1);">Description</th>
              <th style="padding:8px;border:1px solid rgba(255,255,255,0.1);">Suggestion</th>
              <th style="padding:8px;border:1px solid rgba(255,255,255,0.1);">Report</th>
              <th style="padding:8px;border:1px solid rgba(255,255,255,0.1);">Location</th>
              <th style="padding:8px;border:1px solid rgba(255,255,255,0.1);">User</th>
              <th style="padding:8px;border:1px solid rgba(255,255,255,0.1);">Deleted At</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($deletedProblems as $d): ?>
            <tr>
              <td style="padding:6px;border:1px solid rgba(255,255,255,0.1);">#<?= (int)$d['problem_id'] ?></td>
              <td style="padding:6px;border:1px solid rgba(255,255,255,0.1);"><?= htmlspecialchars($d['category']) ?></td>
              <td style="padding:6px;border:1px solid rgba(255,255,255,0.1);"><?= nl2br(htmlspecialchars($d['description'])) ?></td>
              <td style="padding:6px;border:1px solid rgba(255,255,255,0.1);"><?= htmlspecialchars($d['suggestion'] ?? '') ?></td>
              <td style="padding:6px;border:1px solid rgba(255,255,255,0.1);"><?= htmlspecialchars($d['report'] ?? '') ?></td>
              <td style="padding:6px;border:1px solid rgba(255,255,255,0.1);"><?= htmlspecialchars($d['location'] ?? '') ?></td>
              <td style="padding:6px;border:1px solid rgba(255,255,255,0.1);"><?= htmlspecialchars($d['user_name'] ?? '') ?></td>
              <td style="padding:6px;border:1px solid rgba(255,255,255,0.1);"><?= htmlspecialchars($d['deleted_at']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p style="color:var(--muted)">No deleted complaints yet.</p>
      <?php endif; ?>
    </div>
  </div>

</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
var dhakaBounds = L.latLngBounds([23.65, 90.30],[23.90, 90.55]);
var map = L.map('map', { center:[23.78,90.40],zoom:12, maxBounds:dhakaBounds,maxBoundsViscosity:1.0, minZoom:11,maxZoom:16 });
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ attribution:'&copy; OpenStreetMap contributors' }).addTo(map);

<?php foreach($problems as $p): if(!empty($p['latitude']) && !empty($p['longitude'])): ?>
L.marker([<?= (float)$p['latitude']?>,<?= (float)$p['longitude']?>]).addTo(map).bindPopup("<b><?= addslashes($p['category']) ?></b><br><?= addslashes($p['description']) ?>");
<?php endif; endforeach; ?> 

document.getElementById('theme-toggle').addEventListener('click', ()=>{
  document.documentElement.classList.toggle('light-theme');
});

// Toggle Deleted Complaints
document.getElementById('toggle-deleted').addEventListener('click', function() {
  const table = document.getElementById('deleted-table');
  if (table.style.display === 'none') {
    table.style.display = 'block';
    this.innerHTML = '<i class="fa fa-eye-slash"></i> Hide Deleted Complaints';
  } else {
    table.style.display = 'none';
    this.innerHTML = '<i class="fa fa-eye"></i> Show Deleted Complaints';
  }
});
</script>
</body>
</html>
