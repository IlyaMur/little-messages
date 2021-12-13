<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\Core\View;

class Login extends \Ilyamur\PhpMvc\Core\Controller
{
    public function newAction()
    {
        View::renderTemplate('Login/new');
    }
}
