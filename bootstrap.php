<?php

declare(strict_types=1);

// Central bootstrap: autoloading + session boot for every entry script.
// Include once at the top of controllers; db_connect.php provides $pdo.

use App\Core\Session;

require_once __DIR__ . '/vendor/autoload.php';

Session::start();