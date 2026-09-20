<?php
session_start();
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>GovConnect — Forgot Password</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
  body {
    margin:0;
    font-family:'Poppins',sans-serif;
    background:linear-gradient(135deg,#1f1c2c,#928dab);
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
  }
  .card {
    background:rgba(20,20,30,0.9);
    padding:40px;
    border-radius:16px;
    box-shadow:0 8px 30px rgba(0,0,0,0.4);
    width:100%;
    max-width:420px;
    color:#fff;
    text-align:center;
  }
  h2 { margin-bottom:20px; font-size:27px; }
  .row { margin-bottom:15px; }
  input {
    width:100%;
    padding:14px;
    border-radius:10px;
    border:none;
    font-size:20px;
    outline:none;
  }
  .btn {
    width:100%;
    padding:14px;
    border-radius:10px;
    border:none;
    font-weight:bold;
    cursor:pointer;
    background:linear-gradient(90deg,#667eea,#764ba2);
    color:#fff;
    font-size:20px;
    margin-top:8px;
    transition:transform 0.2s;
  }
  .btn:hover { transform:scale(1.02); }
  .link { display:block; margin-top:16px; font-size:18px; color:#ddd; text-decoration:none; }
  .link:hover { text-decoration:underline; }

  .message { padding:12px; border-radius:8px; margin-bottom:15px; font-size:18px; }
  .error { background:#ffdddd; color:#900; }
  .success { background:#ddffdd; color:#060; }
</style>
</head>
<body>

<div class="card">
  <h2><i class="fa-solid fa-lock"></i> Forgot Password</h2>

  <?php if($error): ?>
    <div class="message error"><?= $error ?></div>
  <?php endif; ?>
  <?php if($success): ?>
    <div class="message success"><?= $success ?></div>
  <?php endif; ?>

  <form method="post" action="forgot_password_process.php">
    <div class="row">
      <input type="email" name="email" placeholder="Enter your account email" required>
    </div>
    <button type="submit" class="btn">Reset Password</button>
  </form>

  <a href="login.php" class="link"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
</div>

</body>
</html>
