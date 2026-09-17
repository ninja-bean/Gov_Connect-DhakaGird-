<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Authenticated-user helpers over the session.
 *
 * Works with the session shape established by login_process.php
 * (user_id, role, name) so existing pages remain unaffected.
 */
final class Auth
{
    /** @return array{user_id: int|string, role: string, name: string}|null */
    public static function user(): ?array
    {
        if (!Session::has('user_id') || !Session::has('role')) {
            return null;
        }

        return [
            'user_id' => Session::get('user_id'),
            'role'    => Session::get('role'),
            'name'    => Session::get('name'),
        ];
    }

    public static function isLoggedIn(): bool
    {
        return Session::has('user_id') && Session::has('role');
    }

    public static function id(): int|string|null
    {
        return Session::get('user_id');
    }

    public static function role(): ?string
    {
        return Session::get('role');
    }

    public static function name(): ?string
    {
        return Session::get('name');
    }

    /**
     * Persist an authenticated user into the session.
     *
     * Re-issues the session id (privilege change) to prevent fixation.
     *
     * @param array{user_id: int|string, role: string, name: string} $user
     */
    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', $user['user_id']);
        Session::set('role', $user['role']);
        Session::set('name', $user['name']);
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    /**
     * Abort the request if the caller is not authenticated as one of $roles.
     *
     * @param list<string> $roles
     */
    public static function requireRole(string ...$roles): void
    {
        if (!self::isLoggedIn()) {
            Redirect::to('login.php');
        }

        if ($roles !== [] && !in_array(self::role(), $roles, true)) {
            Redirect::to('dashboard.php');
        }
    }
}