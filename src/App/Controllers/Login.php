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
}
