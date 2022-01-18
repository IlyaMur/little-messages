<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Models\User;

class Account extends BaseController
{
    public function validateEmailAction()
    {
        $isValid = !User::emailExists($_GET['email'], $_GET['ignoreId'] ?? null);

        header('Content-Type: application/json');

        echo json_encode($isValid);
    }
}
