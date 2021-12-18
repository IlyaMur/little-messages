<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

abstract class Authenticated extends \Ilyamur\PhpMvc\Core\Controller
{
    protected function before()
    {
        $this->requireLogin();
    }
}
