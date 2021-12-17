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
        User::sendPasswordRequest($_POST['inputEmail']);

        View::renderTemplate('password/reset_requested.html');
    }

    public function resetAction(): void
    {
        $token = $this->routeParams['token'];

        if ($this->checkTokenAndGetUser($token)) {
            View::renderTemplate('password/reset.html', ['token' => $token]);
        };
    }

    public function resetPasswordAction()
    {
        $token = $_POST['token'];

        if ($this->checkTokenAndGetUser($token)) {
            echo 'reset password';
        }
    }

    protected function checkTokenAndGetUser($token): ?User
    {
        $user = User::findByPasswordReset($token);

        if ($user) {
            return $user;
        }

        View::renderTemplate('password/token_expired.html');
        return null;
    }
}
