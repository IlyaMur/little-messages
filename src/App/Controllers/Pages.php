<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\Config\Config;

class Pages extends \Ilyamur\PhpMvc\Core\Controller
{
    public function aboutAction()
    {
        View::renderTemplate('pages/about.html');
    }
}
