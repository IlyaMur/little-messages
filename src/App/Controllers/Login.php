<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\User;

class Login extends \Ilyamur\PhpMvc\Core\Controller
{
    public function newAction()
    {
        View::renderTemplate('Login/new');
    }

    public function createAction()
    {
        $user = User::findByEmail($_POST['email']);
    }
}
