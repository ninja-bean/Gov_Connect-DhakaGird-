<?php

declare(strict_types=1);

namespace App\Tests\Support;

use PDO;

/**
 * Provides a PDO handle to the real local g1 database for integration tests.
 *
 * Credentials come from the gitignored .env via the app's own bootstrap, so
 * tests exercise the same data paths as the running application.
 */
final class Db
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo === null) {
            require_once dirname(__DIR__, 2) . '/db_connect.php'; // provides $pdo
            self::$pdo = $pdo;
        }

        return self::$pdo;
    }
}