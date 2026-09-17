<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DhakaGrid — Login</title>

    <link rel="stylesheet" href="assets/css/dhakagrid.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { align-items: center; justify-content: center; overflow: auto; }
    </style>
</head>
<body class="sketch-body">

    <?php $sketch_theme = 'day'; include __DIR__ . '/assets/partials/sketch_bg.php'; ?>

    <div class="auth-wrap">
        <div class="glass-card">

            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <h1 class="logo-title">DhakaGrid</h1>
                <p class="logo-subtitle">City Management Portal</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="tab-container">
                <button class="tab-btn active" onclick="switchTab('citizen')" data-tab="citizen">
                    <i class="fa-solid fa-user"></i><span>Citizen</span>
                </button>
                <button class="tab-btn" onclick="switchTab('response')" data-tab="response">
                    <i class="fa-solid fa-truck-medical"></i><span>Response</span>
                </button>
                <button class="tab-btn" onclick="switchTab('admin')" data-tab="admin">
                    <i class="fa-solid fa-shield-halved"></i><span>Admin</span>
                </button>
            </div>

            <!-- Citizen Form -->
            <div class="form-container active" id="form-citizen">
                <form method="POST" action="login_process.php">
                    <input type="hidden" name="role" value="user">

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-envelope"></i> Email Address</label>
                        <input type="email" name="email" class="form-input" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-lock"></i> Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-right-to-bracket"></i> Sign In
                    </button>
                </form>
            </div>

            <!-- Response Team Form -->
            <div class="form-container" id="form-response">
                <form method="POST" action="login_process.php">
                    <input type="hidden" name="role" value="response">

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-id-badge"></i> Unit Email Address</label>
                        <input type="text" name="email" class="form-input" placeholder="Enter unit identifier" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-key"></i> Security Key</label>
                        <input type="password" name="password" class="form-input" placeholder="Enter security key" required>
                    </div>

                    <button type="submit" class="btn-submit btn-danger">
                        <i class="fa-solid fa-truck-medical"></i> Deploy Unit
                    </button>
                </form>
            </div>

            <!-- Admin Form -->
            <div class="form-container" id="form-admin">
                <form method="POST" action="login_process.php">
                    <input type="hidden" name="role" value="admin">

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-user-shield"></i> Admin ID</label>
                        <input type="text" name="email" class="form-input" placeholder="Enter admin code" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-shield-halved"></i> System Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Enter system password" required>
                    </div>

                    <button type="submit" class="btn-submit btn-sky">
                        <i class="fa-solid fa-terminal"></i> Access Terminal
                    </button>
                </form>
            </div>

            <!-- Footer Link -->
            <div class="footer-link">
                <a href="register.php">
                    <i class="fa-solid fa-user-plus"></i> Create New Account
                </a>
            </div>

        </div>
    </div>

    <script>
        const skyBg = document.getElementById('skyBackground');
        const celestialBody = skyBg ? skyBg.querySelector('.celestial') : null;

        function switchTab(tabName) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

            document.querySelectorAll('.form-container').forEach(form => form.classList.remove('active'));
            document.getElementById(`form-${tabName}`).classList.add('active');

            if (!skyBg) return;
            skyBg.classList.remove('day', 'sunset', 'night');
            if (celestialBody) celestialBody.classList.remove('sunset-sun', 'moon');

            if (tabName === 'citizen') {
                skyBg.classList.add('day');
            } else if (tabName === 'response') {
                skyBg.classList.add('sunset');
                if (celestialBody) celestialBody.classList.add('sunset-sun');
            } else if (tabName === 'admin') {
                skyBg.classList.add('night');
                if (celestialBody) celestialBody.classList.add('moon');
            }
        }
    </script>

</body>
</html>