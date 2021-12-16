<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\User;

class Password extends \Ilyamur\PhpMvc\Core\Controller
{
    public function forgotAction(): void
    {
        View::renderTemplate('password/forgot');
    }

    public function requestResetAction(): void
    {
        User::sendPasswordRequest($_POST['inputEmail']);

        View::renderTemplate('password/reset_requested');
    }
}
