<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

abstract class Authenticated extends BaseController
{
    protected function before(): void
    {
        $this->requireLogin();
    }
}
