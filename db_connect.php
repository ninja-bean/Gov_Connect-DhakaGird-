<?php

declare(strict_types=1);

// db_connect.php
// Database bootstrap: credentials come from the gitignored .env via App\Core\Config.
// Put this file in the project root (same folder as your other php files).

use App\Core\Config;

require_once __DIR__ . '/vendor/autoload.php';

$db = Config::db();

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $db['host'],
    $db['port'],
    $db['name']
);

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], $options);
} catch (PDOException $e) {
    // In dev you can show error; in production log instead.
    die("Database connection failed: " . $e->getMessage());
}