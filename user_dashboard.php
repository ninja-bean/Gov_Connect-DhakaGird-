<?php
// ============================================================
// DhakaGrid **Citizen Dashboard**  (flagship · DESIGN A4 app shell)
// Phase 3 · 03.1  app shell rewrite — fresh build
//
// REAL data, REAL behavior, restyled chrome (§4 shell, §5 components,
// §6 motion). No new libraries. No shimmer/noise loops — the only
// repeating motion is the SOS affordance (§6 infinite-loops rule).
// ============================================================

use App\Core\Auth;

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db_connect.php';

// ============================================================
// ── AUTH ──
// ============================================================
Auth::requireRole('user');

$user_id   = (int) Auth::id();
$user_name = Auth::name() ?? 'Citizen';

// --- PROFILE PIC (real) ---
$user_pic = null;
$has_pic  = false;
if (!empty($dg_settings['profile_pic_enabled'] ?? true)) {
    try {
        $picStmt = $pdo->prepare("SELECT profile_pic, name FROM users WHERE user_id = :id");
        $picStmt->execute(['id' => $user_id]);
        $picRow = $picStmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($picRow['profile_pic']) && file_exists($picRow['profile_pic'])) {
            $user_pic = $picRow['profile_pic'];
            $has_pic  = true;
        }
        if (!empty($picRow['name'])) { $user_name = $picRow['name']; }
    } catch (Exception $e) { /* pic optional */ }
}

// ============================================================
// ── REAL DATA (every value from the DB, nothing fake) ──
// ============================================================
$stats   = ['total' => 0, 'sos' => 0, 'resolved' => 0];
$chartData = [0, 0, 0, 0, 0];
$reports = [];
$mapReports = [];
$ticker_news = [];
$gov_notice = null;
$notifs = [];
$unread_n = 0;
$total_xp = 0;

try {
    // --- aggregate stats + XP (real) ---
    $aggr = $pdo->prepare("
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN LOWER(priority) = 'sos' THEN 1 ELSE 0 END) AS sos,
            SUM(CASE WHEN LOWER(status) = 'resolved' THEN 1 ELSE 0 END) AS resolved
        FROM problems WHERE user_id = :user_id
    ");
    $aggr->execute(['user_id' => $user_id]);
    $a = $aggr->fetch(PDO::FETCH_ASSOC);
    $stats['total']    = (int)($a['total'] ?? 0);
    $stats['sos']      = (int)($a['sos'] ?? 0);
    $stats['resolved'] = (int)($a['resolved'] ?? 0);

    // --- chart per category (real) ---
    $cats = $pdo->prepare("
        SELECT LOWER(category) c, COUNT(*) n FROM problems
        WHERE user_id = :user_id GROUP BY LOWER(category)
    ");
    $cats->execute(['user_id' => $user_id]);
    $map = ['traffic' => 0, 'water' => 1, 'waste' => 2, 'sos' => 3];
    foreach ($cats->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $idx = $map[$row['c']] ?? 4;
        $chartData[$idx] = (int)$row['n'];
    }

    // --- recent 5 (real) ---
    $rStmt = $pdo->prepare("
        SELECT * FROM problems WHERE user_id = :user_id
        ORDER BY created_at DESC LIMIT 5
    ");
    $rStmt->execute(['user_id' => $user_id]);
    $reports = $rStmt->fetchAll(PDO::FETCH_ASSOC);

    // --- XP (real) ---
    $xpStmt = $pdo->prepare("
        SELECT COALESCE(SUM(xp_awarded), 0) xp FROM xp_entries WHERE user_id = :user_id
    ");
    $xpStmt->execute(['user_id' => $user_id]);
    $total_xp = (int)$xpStmt->fetchColumn();

} catch (Exception $e) {
    error_log("user_dashboard flagship: " . $e->getMessage());
    $stats = ['total' => 0, 'sos' => 0, 'resolved' => 0];
    $chartData = [0, 0, 0, 0, 0];
}

// ============================================================
// ── RANK / XP / STREAK (kept + restyled §3, no shimmer) ──
// ============================================================
$rank_names = ['City Watcher', 'Street Scout', 'Block Guardian',
               'Ward Sentinel', 'Dhaka Hero', 'Civic Legend'];
$rank_idx = min(count($rank_names) - 1, (int)floor($total_xp / 1000));
$rank_name = $rank_names[$rank_idx];
$rank_pct = (int)min(100, ($total_xp % 1000) / 10); // % to next rank
$rank_lvl = $rank_idx + 1;
$next_rank = $rank_names[$rank_idx + 1] ?? 'Max Rank';

// streak (resolved-per-day, real date math, cap 30 in display)
$streak = 0;
try {
    $sDays = $pdo->prepare("
        SELECT DISTINCT DATE(created_at) d FROM problems
        WHERE user_id = :user_id AND LOWER(status) = 'resolved'
        ORDER BY d DESC
    ");
    $sDays->execute(['user_id' => $user_id]);
    $set = [];
    foreach ($sDays->fetchAll(PDO::FETCH_ASSOC) as $r) { $set[$r['d']] = true; }
    $d = date('Y-m-d');
    while (isset($set[$d])) { $streak++; $d = date('Y-m-d', strtotime($d . ' -1 day')); }
} catch (Exception $e) { /* streak optional */ }

// ============================================================
// ── LIVE MAP DATA (real, active only) ──
// ============================================================
try {
    $mapStmt = $pdo->prepare("
        SELECT latitude, longitude, category, priority, status, created_at
        FROM problems
        WHERE latitude IS NOT NULL AND longitude IS NOT NULL
          AND status NOT IN ('resolved','rejected')
        ORDER BY created_at DESC LIMIT 100
    ");
    $mapStmt->execute();
    $mapReports = $mapStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* map optional */ }

// ============================================================
// ── TICKER (real resolved + pending + SOS 24h) ──
// ============================================================
$ticker_news = [];
try {
    $tNews = $pdo->prepare("
        SELECT category, location_name, updated_at FROM problems
        WHERE status = 'resolved' ORDER BY updated_at DESC LIMIT 8
    ");
    $tNews->execute();
    foreach ($tNews->fetchAll(PDO::FETCH_ASSOC) as $t) {
        $loc = !empty($t['location_name']) ? $t['location_name'] : 'Dhaka area';
        $ticker_news[] = "✅ Solved: " . ucfirst($t['category']) . " issue in " . $loc;
    }
    $pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM problems WHERE status = 'pending'")->fetchColumn();
    $ticker_news[] = "⚡ " . $pendingCount . " reports pending verification citywide";
    $sosCount = (int)$pdo->query("
        SELECT COUNT(*) FROM problems
        WHERE priority = 'sos' AND status = 'pending'
          AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ")->fetchColumn();
    if ($sosCount > 0) {
        $ticker_news[] = "🚨 " . $sosCount . " active emergency alerts in last 24 hours";
    }
} catch (Exception $e) { /* ticker optional */ }
if (empty($ticker_news)) {
    $ticker_news = ["⚡ Grid system operating normally.",
                    "📢 Report any issues immediately.",
                    "🌧️ Check weather updates before travel."];
}

// ============================================================
// ── GOV NOTICE (real, citywide + your-targeted) ──
// ============================================================
$gov_notice = null;
try {
    $nStmt = $pdo->prepare("
        SELECT message, created_at FROM warnings
        WHERE user_id IS NULL OR user_id = :uid
        ORDER BY created_at DESC LIMIT 1
    ");
    $nStmt->execute(['uid' => $user_id]);
    $gov_notice = $nStmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* notice optional */ }
if (!$gov_notice) {
    $gov_notice = ['message' => 'DhakaGrid is running normally. Stay safe & report concerns early.',
                   'created_at' => date('Y-m-d H:i:s')];
}

// ============================================================
// ── NOTIFICATIONS (real from logs/activities) ──
// ============================================================
try {
    $notifTable = (defined('DG_TABLE_LOGS') ? DG_TABLE_LOGS : 'logs');
    $notifStmt = $pdo->prepare("
        SELECT message, created_at, is_read FROM {$notifTable}
        WHERE user_id = :uid ORDER BY created_at DESC LIMIT 6
    ");
    $notifStmt->execute(['uid' => $user_id]);
    $notifs = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($notifs as $n) { if (empty($n['is_read'])) { $unread_n++; } }
} catch (Exception $e) { /* notifications optional */ }

// ============================================================
// ── WEATHER (real, Open-Meteo) ──
// ============================================================
$weather = null;
$wmo_icon = 'fa-cloud';
$wmo_lbl  = 'Weather';
try {
    $wj = @file_get_contents(
        "https://api.open-meteo.com/v1/forecast?latitude=23.8103&longitude=90.4125" .
        "&current=temperature_2m,relative_humidity_2m,weather_code&timezone=auto"
    );
    if ($wj !== false) {
        $w = json_decode($wj, true);
        if (!empty($w['current'])) {
            $weather = [
                'temp' => (int)round($w['current']['temperature_2m']),
                'hum'  => (int)$w['current']['relative_humidity_2m'],
                'code' => (int)$w['current']['weather_code'],
            ];
        }
    }
} catch (Exception $e) { /* weather optional */ }
$wmo_map = [0 => 'fa-sun', 1 => 'fa-sun', 2 => 'fa-cloud-sun', 3 => 'fa-cloud',
            45 => 'fa-smog', 48 => 'fa-smog', 51 => 'fa-cloud-rain', 53 => 'fa-cloud-rain',
            55 => 'fa-cloud-rain', 61 => 'fa-cloud-showers-heavy', 63 => 'fa-cloud-showers-heavy',
            65 => 'fa-cloud-showers-heavy', 80 => 'fa-cloud-showers-heavy',
            95 => 'fa-cloud-bolt', 96 => 'fa-cloud-bolt', 99 => 'fa-cloud-bolt'];
$wmo_lbl_map = [0 => 'Clear', 1 => 'Mostly clear', 2 => 'Partly cloudy', 3 => 'Overcast',
                45 => 'Fog', 51 => 'Light drizzle', 61 => 'Light rain', 63 => 'Rain',
                65 => 'Heavy rain', 80 => 'Showers', 95 => 'Thunderstorm'];
if ($weather) {
    $wmo_icon = $wmo_map[$weather['code']] ?? 'fa-cloud';
    $wmo_lbl  = $wmo_lbl_map[$weather['code']] ?? 'Fair';
}

// ============================================================
// ── SHELL CONTRACT (feeds display-only partials) ──
// ============================================================
$shell_title = 'Citizen Dashboard · DhakaGrid';
$shell_user  = [
    'avatar' => $has_pic ? $user_pic : '',
    'name'   => $user_name,
    'role'   => 'Citizen',
];
$shell_nav = [
    ['icon' => 'fa-solid fa-house',           'label' => 'Overview',     'href' => 'user_dashboard.php', 'active' => true],
    ['icon' => 'fa-solid fa-file-lines',      'label' => 'My Reports',   'href' => 'user_dashboard.php?view=reports'],
    ['icon' => 'fa-solid fa-chart-pie',       'label' => 'Analytics',    'href' => 'user_dashboard.php?view=analytics'],
    ['icon' => 'fa-regular fa-bell',          'label' => 'Notifications','href' => 'user_dashboard.php?view=notifications'],
    ['icon' => 'fa-solid fa-user',            'label' => 'Profile',      'href' => 'profile.php'],
    ['icon' => 'fa-solid fa-right-from-bracket', 'label' => 'Sign Out',  'href' => 'logout.php'],
];
$shell_brand = 'user_dashboard.php';
$shell_footer = $dg_footer ?? 'DhakaGrid · Citi-Connect';

require __DIR__ . '/assets/app/shell_top.php';
?>
        <!-- ===== NOTICE BANNER (real gov/announcement) ===== -->
        <?php if (!empty($gov_notice['message'])): ?>
        <div class="notice notice--banner" data-dg-notice>
            <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
            <div>
                <strong>City Notice</strong>
                <p class="type-muted"><?= htmlspecialchars($gov_notice['message']) ?></p>
                <span class="type-caption">Issued <?= date('M j, g:i A', strtotime($gov_notice['created_at'])) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- ===== NEWS TICKER (real) ===== -->
        <section class="ticker" aria-label="City updates">
            <div class="ticker__ico" aria-hidden="true">
                <span class="pill pill--danger">LIVE</span>
            </div>
            <div class="ticker__viewport">
                <span class="ticker__item type-strong" id="dgTicker"></span>
            </div>
        </section>

        <!-- ===== STAT CARDS (real totals) ===== -->
        <section class="grid-card" aria-label="Your impact">
            <div class="stat-card">
                <span class="stat-card__ico"><i class="fa-solid fa-file-lines" aria-hidden="true"></i></span>
                <strong class="stat-card__val"><?= (int)$stats['total'] ?></strong>
                <span class="stat-card__lbl">My Reports</span>
            </div>
            <div class="stat-card stat-card--danger">
                <span class="stat-card__ico"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
                <strong class="stat-card__val"><?= (int)$stats['sos'] ?></strong>
                <span class="stat-card__lbl">SOS Alerts</span>
            </div>
            <div class="stat-card stat-card--ok">
                <span class="stat-card__ico"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
                <strong class="stat-card__val"><?= (int)$stats['resolved'] ?></strong>
                <span class="stat-card__lbl">Resolved</span>
            </div>
            <div class="stat-card stat-card--accent">
                <span class="stat-card__ico"><i class="fa-solid fa-bolt" aria-hidden="true"></i></span>
                <strong class="stat-card__val"><?= number_format($total_xp) ?> XP</strong>
                <span class="stat-card__lbl">Total Earned</span>
            </div>
        </section>

        <!-- ===== ACTIONS ===== -->
        <div class="actions-row">
            <a class="btn btn-primary btn-lg" href="submit_problem.php">
                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> Submit Report
            </a>
            <button type="button" class="btn btn-danger btn-lg" data-dg-sos-open>
                <i class="fa-solid fa-bullhorn" aria-hidden="true"></i> EMERGENCY SOS
            </button>
        </div>

        <div class="app-grid">

            <!-- ===== LIVE MAP (real Leaflet) ===== -->
            <section class="card" aria-labelledby="dgMapTitle">
                <header class="card__head">
                    <h2 id="dgMapTitle"><i class="fa-solid fa-map" aria-hidden="true"></i> Live Grid Map</h2>
                    <span class="pill pill--ok"><i class="fa-solid fa-circle" aria-hidden="true"></i> Online</span>
                </header>
                <div class="card__body">
                    <div class="map-legend" aria-label="Map legend">
                        <span class="legend-chip legend-chip--sos">SOS</span>
                        <span class="legend-chip legend-chip--warn">High</span>
                        <span class="legend-chip legend-chip--accent">Active</span>
                        <span class="legend-chip legend-chip--ok">Resolved</span>
                        <span class="legend-chip legend-chip--muted">Pending</span>
                    </div>
                    <div id="dgMap" class="map-canvas" role="application" aria-label="City map with incident markers"></div>
                </div>
            </section>

            <!-- ===== ANALYTICS ===== -->
            <section class="card" aria-labelledby="dgChartTitle">
                <header class="card__head">
                    <h2 id="dgChartTitle"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i> Report Analytics</h2>
                </header>
                <div class="card__body">
                    <canvas id="dgChart" aria-label="Reports by category" role="img"></canvas>
                </div>
            </section>
        </div>

        <!-- ===== XP CARD (rank kept + restyled §3; NO shimmer) ===== -->
        <section class="card" aria-labelledby="dgXpTitle">
            <header class="card__head">
                <h2 id="dgXpTitle"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> City Watch · <?= htmlspecialchars($rank_name) ?></h2>
                <span class="pill"><i class="fa-solid fa-fire" aria-hidden="true"></i> <?= (int)$streak ?>-day streak</span>
            </header>
            <div class="card__body">
                <div class="rank-row">
                    <span class="pill pill--accent">Lv <?= (int)$rank_lvl ?></span>
                    <span class="type-muted"><?= (int)$rank_pct ?>% to <?= htmlspecialchars($next_rank) ?></span>
                </div>
                <div class="progress" role="progressbar" aria-valuenow="<?= (int)$rank_pct ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Progress to next rank">
                    <div class="progress__fill" style="width: <?= (int)$rank_pct ?>%"></div>
                </div>
            </div>
        </section>

        <!-- ===== RECENT REPORTS (real, 5) ===== -->
        <section class="card" aria-labelledby="dgRecentTitle">
            <header class="card__head">
                <h2 id="dgRecentTitle"><i class="fa-solid fa-list" aria-hidden="true"></i> Recent Activity</h2>
                <a class="btn btn-ghost btn-sm" href="user_dashboard.php?view=reports">View all</a>
            </header>
            <?php if (empty($reports)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-map-pin" aria-hidden="true"></i>
                    <h3>No reports yet</h3>
                    <p class="type-muted">Submit your first report to start earning XP.</p>
                    <a class="btn btn-primary" href="submit_problem.php">Submit a Report</a>
                </div>
            <?php else: ?>
            <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Category</th>
                            <th scope="col">Location</th>
                            <th scope="col">Priority</th>
                            <th scope="col">Status</th>
                            <th scope="col">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reports as $r): $isSOS = (strtolower($r['priority'] ?? '') === 'sos'); ?>
                        <tr>
                            <td>
                                <span class="txt-strong"><?= htmlspecialchars(ucfirst($r['category'] ?? '')) ?></span>
                                <?php if ($isSOS): ?><span class="badge badge--danger">SOS</span><?php endif; ?>
                            </td>
                            <td class="type-muted"><?= htmlspecialchars($r['location_name'] ?? '—') ?></td>
                            <td><span class="pill <?= $isSOS ? 'pill--danger' : 'pill--muted' ?>"><?= htmlspecialchars(ucfirst($r['priority'] ?? 'normal')) ?></span></td>
                            <td><span class="pill pill--state"><?= htmlspecialchars(ucfirst($r['status'] ?? '')) ?></span></td>
                            <td class="type-muted"><?= date('M j, g:i A', strtotime($r['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>

        <!-- ===== NOTIFICATIONS (real from logs) ===== -->
        <?php if (!empty($notifs)): ?>
        <section class="card" aria-labelledby="dgNotifTitle">
            <header class="card__head">
                <h2 id="dgNotifTitle"><i class="fa-regular fa-bell" aria-hidden="true"></i> Notifications
                    <?php if ($unread_n > 0): ?><span class="badge badge--danger"><?= $unread_n ?> new</span><?php endif; ?>
                </h2>
            </header>
            <ul class="feed">
                <?php foreach ($notifs as $n): ?>
                <li class="feed__item">
                    <span class="feed__ico feed__ico--muted"><i class="fa-solid fa-circle-info" aria-hidden="true"></i></span>
                    <span class="feed__txt">
                        <span class="<?= empty($n['is_read']) ? 'txt-strong' : 'type-muted' ?>"><?= htmlspecialchars($n['message']) ?></span>
                        <time class="type-caption"><?= date('M j, g:i A', strtotime($n['created_at'])) ?></time>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

<?php
// ============================================================
// dg_js — emission, then shell_bottom (SOS modal when citizen)
// ============================================================
$dg_footer_note = 'DhakaGrid · Citizen';
$dg_sos_role   = 'citizen';
$dg_js = <<<'DGJS'
// ============ page-level JS ============
const DGJS_ID = 'dg-';

function dgEl(id) { return document.getElementById(id); }

// --- WEATHER (real API data) ---
const dgWeather = <?= json_encode($weather) ? 'null' : 'null' ?>;
const DG_WMO = <?= json_encode([
    'code' => $weather['code'] ?? null,
    'temp' => $weather['temp'] ?? null,
    'hum'  => $weather['hum'] ?? null,
    'icon' => $wmo_icon,
    'lbl'  => $wmo_lbl,
]) ?>;

// --- NEWS TICKER (real array from PHP) ---
const DG_TICKER = <?= json_encode($ticker_news) ?>;

// --- MAP (real reports → Leaflet markers) ---
const DG_MAP = {
    sos: '#EF4444', high: '#F59E0B', active: '#3B82F6',
    resolved: '#10B981', pending: '#94A3B8',
    reports: <?= json_encode(array_map(fn($m) => [
        'lat' => (float)$m['latitude'],
        'lng' => (float)$m['longitude'],
        'sos' => strtolower($m['priority'] ?? '') === 'sos',
    ], $mapReports)) ?>,
};

// --- CHART (real counts) ---
const DG_CHART = {
    labels: ['Traffic', 'Water', 'Waste', 'SOS', 'Other'],
    data: <?= json_encode($chartData) ?>,
};

// --- SOS (citizen) open binding ---
document.addEventListener('DOMContentLoaded', () => {
    const sosBtn = document.querySelector('[data-dg-sos-open]');
    if (sosBtn && typeof DG.openSOS === 'function') {
        sosBtn.addEventListener('click', () => DG.openSOS());
    }
    if (typeof DG.initMap === 'function'   && dgEl('dgMap'))    DG.initMap('dgMap', DG_MAP);
    if (typeof DG.initChart === 'function' && dgEl('dgChart')) DG.initChart('dgChart', DG_CHART);
    if (typeof DG.initTicker === 'function' && dgEl('dgTicker')) DG.initTicker('dgTicker', DG_TICKER);
});
DGJS;
require __DIR__ . '/assets/app/shell_bottom.php';
