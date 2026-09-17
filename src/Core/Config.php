<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Typed accessors over environment values.
 *
 * Application settings (DB credentials, etc.) live in a gitignored `.env`
 * file and are read here. Credentials never appear in committed PHP code.
 */
final class Config
{
    public const DB_HOST = 'DB_HOST';
    public const DB_PORT = 'DB_PORT';
    public const DB_NAME = 'DB_NAME';
    public const DB_USER = 'DB_USER';
    public const DB_PASS = 'DB_PASS';

    /**
     * Database connection details, resolved from the environment.
     *
     * @return array{host: string, port: string, name: string, user: string, pass: string}
     */
    public static function db(): array
    {
        $keys = [
            'host' => self::DB_HOST,
            'name' => self::DB_NAME,
            'user' => self::DB_USER,
            'pass' => self::DB_PASS,
        ];

        $db = [];

        foreach ($keys as $name => $key) {
            $value = Env::get($key);

            if ($value === null || $value === '') {
                throw new RuntimeException(sprintf(
                    'Missing required environment value "%s". Check your .env file (see .env.example).',
                    $key
                ));
            }

            $db[$name] = $value;
        }

        $db['port'] = Env::get(self::DB_PORT, '3306');

        return $db;
    }
}