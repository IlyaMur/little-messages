<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\User;

class Password extends \Ilyamur\PhpMvc\Core\Controller
{
    public function forgotAction(): void
    {
        View::renderTemplate('password/forgot.html');
    }

    public function requestResetAction(): void
    {
        if (empty($_POST['inputEmail'])) {
            return;
        }
        User::sendPasswordRequest($_POST['inputEmail']);
        View::renderTemplate('password/reset_requested.html');
    }

    public function resetAction(): void
    {
        $token = $this->routeParams['token'];

        if ($this->findUserAndExit($token)) {
            View::renderTemplate('password/reset.html', ['token' => $token]);
        };
    }

    public function resetPasswordAction(): void
    {

        $token = $_POST['token'];
        $user = $this->findUserAndExit($token);

        if ($user->resetPassword($_POST['password'])) {
            View::renderTemplate('password/reset_success.html');
        } else {
            View::renderTemplate('password/reset.html', ['token' => $token, 'user' => $user]);
        }
    }

    protected function findUserAndExit($token)
    {
        $user = User::findByPasswordReset($token);

        if ($user) {
            return $user;
        }

        View::renderTemplate('password/token_expired.html');
        exit;
    }
}
