<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Views\BaseView;
use Ilyamur\PhpMvc\Models\User;

class Password extends BaseController
{
    public function forgotAction(): void
    {
        BaseView::renderTemplate('password/forgot.html');
    }

    public function requestResetAction(): void
    {
        if (empty($_POST['inputEmail'])) {
            return;
        }
        User::sendPasswordRequest($_POST['inputEmail']);
        BaseView::renderTemplate('password/reset_requested.html');
    }

    public function resetAction(): void
    {
        $token = $this->routeParams['token'];

        if ($this->findUserAndExit($token)) {
            BaseView::renderTemplate('password/reset.html', ['token' => $token]);
        };
    }

    public function resetPasswordAction(): void
    {

        $token = $_POST['token'];
        $user = $this->findUserAndExit($token);

        if ($user->resetPassword($_POST['password'])) {
            BaseView::renderTemplate('password/reset_success.html');
        } else {
            BaseView::renderTemplate('password/reset.html', ['token' => $token, 'user' => $user]);
        }
    }

    protected function findUserAndExit($token)
    {
        $user = User::findByPasswordReset($token);

        if ($user) {
            return $user;
        }

        BaseView::renderTemplate('password/token_expired.html');
        exit;
    }
}
