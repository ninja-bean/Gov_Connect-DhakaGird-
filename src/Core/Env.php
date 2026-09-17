<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal, dependency-free .env loader.
 *
 * Reads KEY=VALUE lines from a single dotenv file (default: project-root/.env).
 * Loads the file at most once per request and caches the parsed map.
 * Only supports the flat `KEY=value` form needed here: no interpolation,
 * no variable expansion, no export handling. Lines are trimmed; blank lines
 * and lines starting with `#` are ignored; surrounding single/double quotes
 * around values are stripped.
 */
final class Env
{
    private const DEFAULT_FILENAME = '.env';

    /** @var array<string, string>|null */
    private static ?array $values = null;

    private ?string $file;

    public function __construct(?string $file = null)
    {
        $this->file = $file;
    }

    /**
     * @return array<string, string>
     */
    public function load(): array
    {
        $path = $this->file ?? $this->projectRoot() . DIRECTORY_SEPARATOR . self::DEFAULT_FILENAME;

        if (!is_file($path) || !is_readable($path)) {
            return [];
        }

        $values = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $separator = strpos($line, '=');

            if ($separator === false) {
                continue;
            }

            $key   = trim(substr($line, 0, $separator));
            $value = trim(substr($line, $separator + 1));

            if ($key === '') {
                continue;
            }

            $values[$key] = self::unquote($value);
        }

        return $values;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        if (self::$values === null) {
            self::$values = (new self())->load();
        }

        return self::$values[$key] ?? $default;
    }

    private static function unquote(string $value): string
    {
        $length = strlen($value);

        if ($length >= 2) {
            $first = $value[0];
            $last  = $value[$length - 1];

            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }

    private function projectRoot(): string
    {
        return dirname(__DIR__, 2);
    }
}