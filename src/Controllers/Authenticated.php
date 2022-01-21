<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

/**
 * Authenticated base controller
 *
 * PHP version 8.0
 */
abstract class Authenticated extends BaseController
{
    /**
     * Require the user to be authenticated before giving access to all methods in the controller
     *
     * @return void
     */
    protected function before(): void
    {
        $this->requireLogin();
    }
}
