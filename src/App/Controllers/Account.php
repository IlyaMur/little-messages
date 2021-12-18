<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Models\User;

class Account extends \Ilyamur\PhpMvc\Core\Controller
{
    public function validateEmailAction()
    {
        $isValid = !User::emailExists($_GET['email'], $_GET['ignoreId'] ?? null);

        header('Content-Type: application/json');

        echo json_encode($isValid);
    }
}
