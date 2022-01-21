<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Service\Auth;
use Ilyamur\PhpMvc\Service\Flash;
use Ilyamur\PhpMvc\Views\BaseView;
use Ilyamur\PhpMvc\Models\User;

/**
 * Login controller
 *
 * PHP version 8.0
 */
class Login extends BaseController
{
    /**
     * Show the login page
     *
     * @return void
     */
    public function newAction(): void
    {
        if (Auth::getUser()) {
            $this->redirect('/');
        };

        BaseView::renderTemplate('Login/new.html');
    }

    /**
     * Log in a user
     *
     * @return void
     */
    public function createAction(): void
    {
        $user = User::authenticate($_POST['email'], $_POST['password']);
        $rememberMe = isset($_POST['rememberMe']);

        if ($user) {
            Auth::login($user, $rememberMe);
            Flash::addMessage('You age logged in');

            $this->redirect(Auth::getReturnToPage());
        }

        Flash::addMessage('Login unsuccessful, please try again', Flash::WARNING);

        BaseView::renderTemplate('Login/new.html', ['email' => $_POST['email'], 'rememberMe' => $rememberMe]);
    }

    /**
     * Log out a user
     *
     * @return void
     */
    public function destroyAction(): void
    {
        Auth::logout();

        $this->redirect('/login/show-logout-message');
    }

    /**
     * Show a "logged out" flash message and redirect to the homepage.
     * 
     * @return void
     */
    public function showLogoutMessageAction()
    {
        Flash::addMessage('Logout successfuly');
        $this->redirect('/');
    }
}
