<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Models\User;

/**
 * Account controller
 *
 * PHP version 8.0
 */
class Account extends BaseController
{
    /**
     * Validate if email is available (AJAX) for a new signup or an existing user.
     *
     * @return void
     */
    public function validateEmailAction()
    {
        $isValid = !User::emailExists($_GET['email'], $_GET['ignoreId'] ?? null);

        header('Content-Type: application/json');

        echo json_encode($isValid);
    }
}
