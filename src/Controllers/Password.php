<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Views\BaseView;
use Ilyamur\PhpMvc\Models\User;

/**
 * Password controller
 *
 * PHP version 8.0
 */
class Password extends BaseController
{
    /**
     * Show the forgotten password page
     *
     * @return void
     */
    public function forgotAction(): void
    {
        BaseView::renderTemplate('password/forgot.html');
    }

    /**
     * Send the password reset link to the supplied email
     *
     * @return void
     */
    public function requestResetAction(): void
    {
        if (empty($_POST['inputEmail'])) {
            return;
        }
        User::sendPasswordRequest($_POST['inputEmail']);
        BaseView::renderTemplate('password/reset_requested.html');
    }

    /**
     * Show the reset password form
     *
     * @return void
     */
    public function resetAction(): void
    {
        $token = $this->routeParams['token'];

        if ($this->getUserOrExit($token)) {
            BaseView::renderTemplate('password/reset.html', ['token' => $token]);
        };
    }

    /**
     * Reset the user's password
     *
     * @return void
     */
    public function resetPasswordAction(): void
    {

        $token = $_POST['token'];
        $user = $this->getUserOrExit($token);

        if ($user->resetPassword($_POST['password'])) {
            BaseView::renderTemplate('password/reset_success.html');
        } else {
            BaseView::renderTemplate('password/reset.html', ['token' => $token, 'user' => $user]);
        }
    }

    /**
     * Find the user model associated with the password reset token, or end the request with a message
     *
     * @param string $token Password reset token sent to user
     *
     * @return mixed User object if found and the token hasn't expired, null otherwise
     */
    protected function getUserOrExit($token)
    {
        $user = User::findByPasswordReset($token);

        if ($user) {
            return $user;
        }

        BaseView::renderTemplate('password/token_expired.html');
        exit;
    }
}
