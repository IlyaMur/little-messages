<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\Core\View;

class Items extends \Ilyamur\PhpMvc\Core\Controller
{
    public function indexAction(): void
    {
        $this->requireLogin();

        View::renderTemplate('Items/index');
    }
}
