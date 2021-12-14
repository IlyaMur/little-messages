<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\Core\View;

class Items extends \Ilyamur\PhpMvc\Core\Controller
{
    public function indexAction()
    {
        if (!Auth::isLoggedIn()) {
            Auth::rememberRequestedPage();
            $this->redirect('/login');
        }

        View::renderTemplate('Items/index');
    }
}
