<?php
session_start();
require_once "db_connect.php";

// --- Authentication ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
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
    <title>Change Password - DhakaGrid</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">

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
            max-width: 600px; 
            margin: 60px auto; 
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
            animation: slideDown 0.3s ease;
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

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* CARD */
        .card { 
            background: var(--surface); 
            border-radius: 16px; 
            border: 1px solid var(--border); 
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .card-head { 
            padding: 28px 32px; 
            border-bottom: 1px solid var(--border); 
            background: linear-gradient(180deg, #FFFFFF 0%, #FAFAFA 100%);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-title i {
            color: var(--accent);
        }

        .card-body {
            padding: 32px;
        }

        /* FORM */
        .form-group {
            margin-bottom: 24px;
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
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .password-input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 14px 50px 14px 16px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-main);
            font-size: 0.95rem;
            font-family: 'Outfit', sans-serif;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.1rem;
            padding: 4px;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: var(--text-main);
        }

        .form-hint {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .password-strength {
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            background: var(--bg);
            font-size: 0.85rem;
            display: none;
        }

        .password-strength.active {
            display: block;
        }

        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: var(--border);
            margin: 8px 0;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: width 0.3s, background 0.3s;
            width: 0%;
        }

        .strength-weak .strength-fill { width: 33%; background: #EF4444; }
        .strength-medium .strength-fill { width: 66%; background: #F59E0B; }
        .strength-strong .strength-fill { width: 100%; background: #10B981; }

        /* BUTTON */
        .btn {
            padding: 16px 28px;
            border-radius: 12px;
            border: none;
            background: var(--primary);
            color: white;
            font-weight: 700;
            font-size: 1.05rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s;
            width: 100%;
        }

        .btn:hover {
            background: #1E293B;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* MODAL - GLASSMORPHISM */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border-radius: 16px;
            padding: 32px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(59, 130, 246, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.8rem;
            color: var(--accent);
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text-main);
        }

        .modal-text {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
        }

        .modal-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-btn-primary {
            background: var(--accent);
            color: white;
        }

        .modal-btn-primary:hover {
            background: #2563EB;
        }

        .modal-btn-secondary {
            background: var(--bg);
            color: var(--text-main);
            border: 1px solid var(--border);
        }

        .modal-btn-secondary:hover {
            background: var(--surface);
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
                margin: 40px auto;
            }

            .header {
                padding: 0 20px;
            }

            .card-body {
                padding: 24px 20px;
            }

            .card-head {
                padding: 24px 20px;
            }
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="brand">
            <i class="fa-solid fa-network-wired"></i> DhakaGrid
        </div>
        <a href="profile.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Back to Profile
        </a>
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
                <div class="card-title">
                    <i class="fa-solid fa-key"></i>
                    Change Password
                </div>
            </div>
            
            <div class="card-body">
                <form id="passwordForm" method="POST" action="change_password_controller.php">
                    
                    <!-- Current Password -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-lock"></i>
                            Current Password
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                name="current_password" 
                                id="currentPassword"
                                class="form-input" 
                                placeholder="Enter your current password"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('currentPassword', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-hint">
                            <i class="fa-solid fa-info-circle"></i>
                            Enter your current password to verify
                        </div>
                    </div>

                    <!-- New Password -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-key"></i>
                            New Password
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                name="new_password" 
                                id="newPassword"
                                class="form-input" 
                                placeholder="Enter new password"
                                required
                                oninput="checkPasswordStrength()"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('newPassword', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        
                        <div class="password-strength" id="strengthIndicator">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <span style="font-weight: 600;">Password Strength:</span>
                                <span id="strengthText">Weak</span>
                            </div>
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                        </div>
                        
                        <div class="form-hint">
                            <i class="fa-solid fa-shield-halved"></i>
                            Use at least 8 characters with letters and numbers
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-check-double"></i>
                            Confirm New Password
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                name="confirm_password" 
                                id="confirmPassword"
                                class="form-input" 
                                placeholder="Re-enter new password"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-hint">
                            <i class="fa-solid fa-rotate"></i>
                            Re-enter your new password to confirm
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="button" class="btn" onclick="showConfirmModal()">
                        <i class="fa-solid fa-shield-halved"></i> Update Password
                    </button>

                </form>
            </div>
        </div>

    </div>

    <footer class="footer">© 2026 Dhaka Grid Control. Connecting citizens.</footer>

    <!-- CONFIRMATION MODAL -->
    <div class="modal" id="confirmModal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h2 class="modal-title">Confirm Password Change</h2>
            <p class="modal-text">
                Are you sure you want to change your password?<br><br>
                You will need to use your new password for future logins.
            </p>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-secondary" onclick="closeModal()">Cancel</button>
                <button class="modal-btn modal-btn-primary" onclick="submitForm()">Confirm Change</button>
            </div>
        </div>
    </div>

    <script>
        // Toggle Password Visibility
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password Strength Checker
        function checkPasswordStrength() {
            const password = document.getElementById('newPassword').value;
            const indicator = document.getElementById('strengthIndicator');
            const text = document.getElementById('strengthText');
            const fill = document.getElementById('strengthFill');
            const strengthBar = indicator.querySelector('.strength-bar');
            
            if (password.length === 0) {
                indicator.classList.remove('active');
                return;
            }
            
            indicator.classList.add('active');
            
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            
            // Character variety checks
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            // Remove all strength classes
            strengthBar.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
            
            // Apply appropriate class
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
                text.textContent = 'Weak';
                text.style.color = '#EF4444';
            } else if (strength <= 4) {
                strengthBar.classList.add('strength-medium');
                text.textContent = 'Medium';
                text.style.color = '#F59E0B';
            } else {
                strengthBar.classList.add('strength-strong');
                text.textContent = 'Strong';
                text.style.color = '#10B981';
            }
        }

        // Show Confirmation Modal
        function showConfirmModal() {
            const form = document.getElementById('passwordForm');
            
            // Validate form first
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Check if passwords match
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;
            
            if (newPass !== confirmPass) {
                alert('New password and confirmation do not match!');
                return;
            }
            
            // Show modal
            document.getElementById('confirmModal').classList.add('active');
        }

        // Close Modal
        function closeModal() {
            document.getElementById('confirmModal').classList.remove('active');
        }

        // Submit Form
        function submitForm() {
            document.getElementById('passwordForm').submit();
        }

        // Close modal on backdrop click
        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>