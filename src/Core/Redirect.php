<?php

declare(strict_types=1);

namespace App\Core;

final class Redirect
{
    public static function to(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    public static function back(): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
        self::to($referer);
    }

    /**
     * Redirect to a path carrying a user-visible message.
     */
    public static function withError(string $path, string $message): never
    {
        self::to($path . '?error=' . urlencode($message));
    }
}