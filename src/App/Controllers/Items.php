<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\Core\View;

class Items extends Authenticated
{
    public function indexAction(): void
    {
        View::renderTemplate('Items/index');
    }
}
