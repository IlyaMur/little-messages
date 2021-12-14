<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\Flash;
use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\User;

class Login extends \Ilyamur\PhpMvc\Core\Controller
{
    public function newAction(): void
    {
        View::renderTemplate('Login/new');
    }

    public function createAction(): void
    {
        $user = User::authenticate($_POST['email'], $_POST['password']);

        if ($user) {
            Auth::login($user);
            Flash::addMessage('You age logged in');

            $this->redirect(Auth::getReturnToPage());
        }
        Flash::addMessage('Login unsuccessful, please try again');

        View::renderTemplate('Login/new', ['email' => $_POST['email']]);
    }

    public function destroyAction(): void
    {
        Auth::logout();

        $this->redirect('/login/show-logout-message');
    }

    public function showLogoutMessageAction()
    {
        Flash::addMessage('Logout successfuly');
        $this->redirect('/');
    }
}
