<?php
session_start();
require_once "db_connect.php";

// auth
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'response') {
  header("Location: login.php");
  exit();
}
$user_id = $_SESSION['user_id'];

// fetch team
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$team = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$team) { echo "Team not found."; exit; }

// flash
$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>GovConnect — Response Profile</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<style>
:root{
  --bg1:#0f172a; --bg2:#1e293b;
  --accent1:#667eea; --accent2:#764ba2;
  --glass: rgba(255,255,255,0.04);
  --muted:#93a0b2;
}
*{box-sizing:border-box}
body{margin:0;font-family:Inter,system-ui,Arial,sans-serif;background:linear-gradient(135deg,var(--bg1),var(--bg2));color:#eaf2ff}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:14px 26px;background:rgba(255,255,255,0.03);backdrop-filter:blur(6px)}
.brand{display:flex;align-items:center;gap:12px;font-weight:700}
.brand .logo{width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,#282a36,#2a5298);display:flex;align-items:center;justify-content:center;font-weight:800}
.container{max-width:1100px;margin:28px auto;padding:0 18px}
.card{background:rgba(255,255,255,0.03);border-radius:14px;padding:26px;border:1px solid var(--glass);box-shadow:0 10px 30px rgba(6,10,18,0.6)}
.form-row{margin-bottom:18px}
.label{font-size:17px;color:var(--muted);margin-bottom:6px;display:block}
.input,.select{width:100%;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.05);color:#fff}
.btn{padding:12px 18px;border-radius:10px;border:0;background:linear-gradient(90deg,var(--accent1),var(--accent2));color:#fff;font-weight:700;cursor:pointer}
.btn:hover{opacity:0.9}
.btn-ghost{padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.1);background:transparent;color:#fff;cursor:pointer}
.alert{padding:12px;border-radius:10px;margin-bottom:12px}
.alert-success{background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.25);color:#dff9e6}
.alert-error{background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);color:#ffdede}
.profile-pic{width:180px;height:180px;border-radius:50%;object-fit:cover;border:4px solid rgba(255,255,255,0.12);background:#0b0f17;margin-bottom:12px}
.upload-btn{display:inline-block;padding:10px 16px;border-radius:10px;background:rgba(255,255,255,0.08);color:#fff;cursor:pointer}
.upload-btn:hover{background:rgba(255,255,255,0.16)}
.hidden-input{display:none}
.small-muted{font-size:16px;color:var(--muted);margin-top:6px}
#mapPicker{height:320px;border-radius:10px;display:none;margin-top:10px;border:1px solid rgba(255,255,255,0.06)}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
@media(max-width:900px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<header class="topbar">
  <div class="brand"><div class="logo">GC</div>GovConnect</div>
  <div>
    <button class="btn-ghost" onclick="location.href='response_dashboard.php'"><i class="fa-solid fa-gauge"></i> Dashboard</button>
    <button class="btn-ghost" onclick="location.href='change_password.php'"><i class="fa-solid fa-key"></i> Change password</button>
    <button class="btn-ghost" onclick="location.href='logout.php'"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
  </div>
</header>

<div class="container">
  <?php if($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="card">
    <h2 style="margin-top:0">Response Team Profile</h2>
    <form id="profileForm" method="post" action="update_response_profile.php" enctype="multipart/form-data">

      <!-- Profile picture -->
      <div style="display:flex;align-items:center;gap:30px;margin-bottom:26px;flex-wrap:wrap">
        <?php
          $picHtml = (!empty($team['profile_pic']) && file_exists(__DIR__."/profile_pics/".$team['profile_pic']))
            ? "profile_pics/".htmlspecialchars($team['profile_pic'])
            : "https://via.placeholder.com/180x180.png?text=Team";
        ?>
        <div>
          <img id="previewImg" src="<?= $picHtml ?>" class="profile-pic" alt="avatar">
        </div>
        <div>
          <label class="upload-btn">
            <i class="fa-solid fa-upload"></i> Change Photo
            <input type="file" id="profilePicInput" name="profile_pic" accept="image/*" class="hidden-input">
          </label>
          <div class="small-muted">Allowed: jpg, jpeg, png, webp (Max 2MB)</div>
        </div>
      </div>

      <!-- Two-column grid -->
      <div class="grid">
        <div class="form-row">
          <label class="label">Team Name</label>
          <input class="input" type="text" name="name" value="<?= htmlspecialchars($team['name']) ?>" required>
        </div>
        <div class="form-row">
          <label class="label">Station Category</label>
          <select class="select" name="category" required>
            <option value="police" <?= $team['category']=='police'?'selected':'' ?>>Police</option>
            <option value="medical" <?= $team['category']=='medical'?'selected':'' ?>>Medical</option>
            <option value="fire" <?= $team['category']=='fire'?'selected':'' ?>>Fire</option>
            <option value="gov" <?= $team['category']=='gov'?'selected':'' ?>>Government</option>
          </select>
        </div>
        <div class="form-row">
          <label class="label">Incharge Name</label>
          <input class="input" type="text" name="incharge_name" value="<?= htmlspecialchars($team['incharge_name']) ?>" required>
        </div>
        <div class="form-row">
          <label class="label">Incharge Email</label>
          <input class="input" type="email" name="incharge_email" value="<?= htmlspecialchars($team['incharge_email']) ?>" required>
        </div>
        <div class="form-row">
          <label class="label">Incharge Phone</label>
          <input class="input" type="text" name="incharge_phone" value="<?= htmlspecialchars($team['incharge_phone']) ?>" required>
        </div>
        <div class="form-row">
          <label class="label">Team Size (Available Members)</label>
          <input class="input" type="number" name="employee_number" min="1" value="<?= (int)$team['employee_number'] ?>" required>
        </div>
        <div class="form-row">
          <label class="label">Station Location</label>
          <input class="input" type="text" id="locationText" name="location" value="<?= htmlspecialchars($team['location']) ?>" readonly required>
          <input type="hidden" id="latitude" name="latitude" value="<?= htmlspecialchars($team['latitude']) ?>">
          <input type="hidden" id="longitude" name="longitude" value="<?= htmlspecialchars($team['longitude']) ?>">
          <div style="margin-top:8px;display:flex;gap:10px;flex-wrap:wrap">
            <button type="button" id="useCurrent" class="btn-ghost"><i class="fa-solid fa-location-crosshairs"></i> Use current</button>
            <button type="button" id="pickOnMap" class="btn-ghost"><i class="fa-solid fa-map-pin"></i> Pick on map</button>
          </div>
          <div id="mapPicker"></div>
        </div>
      </div>

      <div style="margin-top:20px">
        <button type="submit" class="btn"><i class="fa-solid fa-save"></i> Save Profile</button>
      </div>
    </form>
  </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
// Dhaka bounds
const dhakaBounds = L.latLngBounds([23.65,90.30],[23.90,90.55]);

// Preview image
const fileInput = document.getElementById('profilePicInput');
const previewImg = document.getElementById('previewImg');
fileInput.addEventListener('change', e=>{
  const f=e.target.files[0]; if(!f) return;
  if(f.size > 2*1024*1024){alert('Image too large (max 2MB)'); fileInput.value=''; return;}
  const reader=new FileReader(); reader.onload=()=>previewImg.src=reader.result; reader.readAsDataURL(f);
});

// Current location
document.getElementById('useCurrent').onclick=()=>{
  if(!navigator.geolocation){alert('Not supported'); return;}
  navigator.geolocation.getCurrentPosition(async pos=>{
    const la=pos.coords.latitude, lo=pos.coords.longitude;
    document.getElementById('latitude').value=la; document.getElementById('longitude').value=lo;
    try{
      const r=await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${la}&lon=${lo}`);
      const j=await r.json();
      document.getElementById('locationText').value=j.display_name||`${la},${lo}`;
    }catch{document.getElementById('locationText').value=`${la},${lo}`;}
  });
};

// Map picker
let pickerMap,pickerMarker;
document.getElementById('pickOnMap').onclick=()=>{
  const div=document.getElementById('mapPicker');
  div.style.display=div.style.display==='block'?'none':'block';
  if(!pickerMap){
    pickerMap=L.map('mapPicker',{center:[23.78,90.40],zoom:12,maxBounds:dhakaBounds,maxBoundsViscosity:1});
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(pickerMap);
    pickerMap.on('click',async e=>{
      const la=e.latlng.lat, lo=e.latlng.lng;
      if(pickerMarker) pickerMap.removeLayer(pickerMarker);
      pickerMarker=L.marker([la,lo]).addTo(pickerMap);
      document.getElementById('latitude').value=la; document.getElementById('longitude').value=lo;
      try{
        const r=await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${la}&lon=${lo}`);
        const j=await r.json();
        document.getElementById('locationText').value=j.display_name||`${la},${lo}`;
      }catch{document.getElementById('locationText').value=`${la},${lo}`;}
    });
  }else setTimeout(()=>pickerMap.invalidateSize(),150);
};
</script>
</body>
</html>
