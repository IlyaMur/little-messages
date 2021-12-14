<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App;

class Auth
{
    public static function login($user): void
    {
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

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['userId']);
    }

    public static function rememberRequestedPage(): void
    {
        $_SESSION['returnTo'] = $_SERVER['REQUEST_URI'];
    }

    public static function getReturnToPage(): string
    {
        return $_SESSION['returnTo'] ?? '/';
    }
}
