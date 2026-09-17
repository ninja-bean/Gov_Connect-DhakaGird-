<?php 
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>DhakaGrid — Register</title>
<link rel="stylesheet" href="assets/css/dhakagrid.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
    body { align-items: flex-start; justify-content: center; }
    .auth-wrap.wide { margin-top: 30px; }
</style>
</head>
<body class="sketch-body">

<?php $sketch_theme = 'day'; include __DIR__ . '/assets/partials/sketch_bg.php'; ?>

<div class="auth-wrap wide">
    <div class="glass-card">

        <div class="logo-section">
            <div class="logo-icon"><i class="fa-solid fa-user-plus"></i></div>
            <h1 class="logo-title">Create Account</h1>
            <p class="logo-subtitle">Join the DhakaGrid city platform</p>
        </div>

        <?php if ($error): ?>
            <div class="error-alert"><i class="fa-solid fa-circle-exclamation"></i><span><?= str_replace(['<b>','</b>'], '', $error) ?></span></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-alert"><i class="fa-solid fa-circle-check"></i><span><?= str_replace(['<b>','</b>'], '', $success) ?></span></div>
        <?php endif; ?>

        <div class="tab-container">
            <button class="tab-btn active" onclick="switchTab('user','day')" data-tab="user">
                <i class="fa-solid fa-user"></i><span>Citizen</span>
            </button>
            <button class="tab-btn" onclick="switchTab('response','sunset')" data-tab="response">
                <i class="fa-solid fa-truck-medical"></i><span>Response Team</span>
            </button>
        </div>

        <!-- Citizen Registration -->
        <div class="form-container active" id="form-user">
            <form method="post" action="register_process.php">
                <input type="hidden" name="role" value="user"/>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label"><i class="fa-solid fa-signature"></i> Full Name</label>
                        <input type="text" name="name" class="form-input" placeholder="Full Name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-envelope"></i> Email Address</label>
                        <input type="email" name="email" class="form-input" placeholder="Email Address" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-phone"></i> Phone Number</label>
                        <input type="text" name="phone" class="form-input" placeholder="Phone Number" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-id-card"></i> National ID</label>
                        <input type="text" name="nid" class="form-input" placeholder="National ID" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-cake-candles"></i> Date of Birth</label>
                        <input type="date" name="dob" class="form-input" required>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label"><i class="fa-solid fa-location-dot"></i> Permanent Address</label>
                        <input type="text" name="location" class="form-input" placeholder="Permanent Address" required>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label"><i class="fa-solid fa-lock"></i> Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Create a strong password" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit"><i class="fa-solid fa-user-plus"></i> Register</button>
            </form>
        </div>

        <!-- Response Team Registration -->
        <div class="form-container" id="form-response">
            <form method="post" action="register_process.php">
                <input type="hidden" name="role" value="response"/>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label"><i class="fa-solid fa-building-shield"></i> Team Name</label>
                        <input type="text" name="name" class="form-input" placeholder="Team Name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-layer-group"></i> Category</label>
                        <select name="category" class="form-select" required>
                            <option value="">— Select Category —</option>
                            <option value="police">Police</option>
                            <option value="medical">Medical</option>
                            <option value="fire">Fire</option>
                            <option value="gov">Government</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-user-tie"></i> Incharge Name</label>
                        <input type="text" name="incharge_name" class="form-input" placeholder="Incharge Name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-id-badge"></i> Incharge ID</label>
                        <input type="text" name="incharge_id" class="form-input" placeholder="Incharge ID" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-envelope"></i> Incharge Email</label>
                        <input type="email" name="incharge_email" class="form-input" placeholder="Incharge Email" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-phone"></i> Incharge Phone</label>
                        <input type="text" name="incharge_phone" class="form-input" placeholder="Incharge Phone" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-fingerprint"></i> Station Code</label>
                        <input type="text" name="identification" class="form-input" placeholder="Station Code" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-location-dot"></i> Location / Address</label>
                        <input type="text" name="location" class="form-input" placeholder="Location / Address" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-phone-volume"></i> Official Team Phone</label>
                        <input type="text" name="phone" class="form-input" placeholder="Official Team Phone" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-envelope-circle-check"></i> Official Team Email</label>
                        <input type="email" name="email" class="form-input" placeholder="Official Team Email" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-users"></i> Number of Employees</label>
                        <input type="number" name="employee_number" class="form-input" min="1" placeholder="Number of Employees" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-lock"></i> Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Create a strong password" required>
                    </div>
                </div>

                <div class="form-hint mb-2">
                    <i class="fa-solid fa-circle-info"></i> Response Team accounts require admin approval. Your status will be <b>Pending</b> until approved.
                </div>

                <button type="submit" class="btn-submit btn-danger"><i class="fa-solid fa-truck-fast"></i> Register Team</button>
            </form>
        </div>

        <div class="footer-link">
            <a href="login.php"><i class="fa-solid fa-arrow-left"></i> Already have an account? Login</a>
        </div>

    </div>
</div>

<script>
    function switchTab(tabName, theme) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        document.querySelectorAll('.form-container').forEach(form => form.classList.remove('active'));
        document.getElementById(`form-${tabName}`).classList.add('active');

        const skyBg = document.getElementById('skyBackground');
        if (skyBg) {
            skyBg.classList.remove('day', 'sunset', 'night');
            skyBg.classList.add(theme);
        }
    }

    const messages = document.querySelectorAll('.error-alert, .success-alert');
    messages.forEach(msg => {
        setTimeout(() => {
            msg.style.transition = 'opacity 0.5s ease';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }, 4000);
    });
</script>

</body>
</html>