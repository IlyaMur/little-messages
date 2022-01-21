<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Service;

use Ilyamur\PhpMvc\Models\RememberedLogin;
use Ilyamur\PhpMvc\Models\User;

/**
 * Authentication
 *
 * PHP version 8.0
 */
class Auth
{
    /**
     * Login the user
     *
     * @param User $user The user model
     * @param boolean $remember_me Remember the login if true
     *
     * @return void
     */
    public static function login($user, $rememberMe): void
    {
        if ($rememberMe && $user->rememberLogin()) {
            setcookie(
                name: 'rememberMe',
                value: $user->rememberToken,
                expires_or_options: $user->expiresAt,
                path: '/'
            );
        }

        session_regenerate_id(true);

        $_SESSION['userId'] = $user->id;
    }

    /**
     * Logout the user
     *
     * @return void
     */
    public static function logout(): void
    {
        $_SESSION = [];

        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destroy the session
        session_destroy();

        static::forgetLogin();
    }

    /**
     * Remember the originally-requested page in the session
     *
     * @return void
     */
    public static function rememberRequestedPage(): void
    {
        $_SESSION['returnTo'] = $_SERVER['REQUEST_URI'];
    }

    /**
     * Get the originally-requested page to return to after requiring login, or default to the homepage
     *
     * @return string
     */
    public static function getReturnToPage(): string
    {
        return $_SESSION['returnTo'] ?? '/';
    }

    /**
     * Get the current logged-in user, from the session or the remember-me cookie
     *
     * @return mixed The user model or null if not logged in
     */
    public static function getUser(): ?User
    {
        if (isset($_SESSION['userId'])) {
            return User::findById((int) $_SESSION['userId']);
        }

        return static::loginFromRememberCookie();
    }

    /**
     * Login the user from a remembered login cookie
     *
     * @return mixed The user model if login cookie found; null otherwise
     */
    protected static function loginFromRememberCookie(): ?User
    {
        $cookie = $_COOKIE['rememberMe'] ?? false;

        if (!$cookie) {
            return null;
        }

        $rememberedLogin = RememberedLogin::findByToken($cookie);

        if ($rememberedLogin && !$rememberedLogin->hasExpired()) {
            $user = $rememberedLogin->getUserByToken();

            static::login($user, false);

            return $user;
        }

        return null;
    }

    /**
     * Forget the remembered login, if present
     *
     * @return void
     */
    public static function forgetLogin(): void
    {
        $cookie = $_COOKIE['rememberMe'] ?? false;

        if (!$cookie) {
            return;
        }

        $rememberedLogin = RememberedLogin::findByToken($cookie);

        if ($rememberedLogin) {
            $rememberedLogin->delete();
        }

        setcookie('rememberMe', '', time() - 3600); // expire cookie
    }
}
