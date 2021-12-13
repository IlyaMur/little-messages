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
        if (User::authenticate($_POST['email'], $_POST['password'])) {
            header('location: http://' . $_SERVER['HTTP_HOST'] . '/', true, 303);
            exit;
        }

        View::renderTemplate('Login/new');
    }
}
