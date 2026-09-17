<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Redirect;

require_once __DIR__ . '/bootstrap.php';

// If user is not logged in, redirect to login
if (!Auth::isLoggedIn()) {
    Redirect::to('login.php');
}

$role = Auth::role();

// Redirect user based on role
switch ($role) {
    case 'user':
        Redirect::to('user_dashboard.php');
    case 'response':
        Redirect::to('response_dashboard.php');
    case 'admin':
        Redirect::to('admin_dashboard.php');
    default:
        // If role is invalid, destroy session and force login again
        Auth::logout();
        Redirect::withError('login.php', 'invalid_role');
}