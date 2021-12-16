<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\Mail;
use \Ilyamur\PhpMvc\Core\View;

class Home extends \Ilyamur\PhpMvc\Core\Controller
{
    public function indexAction()
    {
        View::renderTemplate('home/index.html');
    }

    public function before()
    {
    }

    public function after()
    {
    }
}
