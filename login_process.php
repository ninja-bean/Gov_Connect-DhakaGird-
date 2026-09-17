<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Redirect;

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db_connect.php'; // creates $pdo (PDO instance)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Redirect::to('login.php');
}

$role = $_POST['role'] ?? 'user';
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    Redirect::withError('login.php', 'Please enter both email and password.');
}

try {
    $stmt = $pdo->prepare('SELECT user_id, name, email, password, role, status FROM users WHERE email = ? AND role = ?');
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        Redirect::withError('login.php', 'Invalid login credentials.');
    }

    if (!password_verify($password, $user['password'])) {
        Redirect::withError('login.php', 'Invalid login credentials.');
    }

    // Check status if Response Team
    if ($role === 'response' && $user['status'] !== 'active') {
        Redirect::withError('login.php', 'Your Response Team account is still pending admin approval.');
    }

    // Login successful
    Auth::login([
        'user_id' => $user['user_id'],
        'role'    => $user['role'],
        'name'    => $user['name'],
    ]);

    Redirect::to('dashboard.php');

} catch (PDOException $e) {
    Redirect::withError('login.php', 'Server error, please try again later.');
}