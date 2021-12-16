<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\Core\View;

class Password extends \Ilyamur\PhpMvc\Core\Controller
{
    public function forgotAction(): void
    {
        View::renderTemplate('password/forgot');
    }
}
