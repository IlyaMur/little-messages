<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\User;

class Login extends \Ilyamur\PhpMvc\Core\Controller
{
    public function newAction(): void
    {
        View::renderTemplate('Login/new');
    }

    public function createAction(): void
    {
        $user = User::authenticate($_POST['email'], $_POST['password']);

        if ($user) {
            $_SESSION['userId'] = $user->id;

            $this->redirect('/');
        }

        View::renderTemplate('Login/new', ['email' => $_POST['email']]);
    }

    public function destroyAction(): void
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

        $this->redirect('/');
    }
}
