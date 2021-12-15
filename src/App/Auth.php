<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App;

use Ilyamur\PhpMvc\App\Models\RememberedLogin;
use Ilyamur\PhpMvc\App\Models\User;

class Auth
{
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

    public static function logout(): void
    {
        $_SESSION = [];

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

        session_destroy();
    }

    public static function rememberRequestedPage(): void
    {
        $_SESSION['returnTo'] = $_SERVER['REQUEST_URI'];
    }

    public static function getReturnToPage(): string
    {
        return $_SESSION['returnTo'] ?? '/';
    }

    public static function getUser(): ?User
    {
        if (isset($_SESSION['userId'])) {
            return User::findById((int) $_SESSION['userId']);
        }

        return static::loginFromRememberCookie();
    }

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
}
