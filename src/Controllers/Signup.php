<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Service\Auth;
use Ilyamur\PhpMvc\Views\BaseView;
use Ilyamur\PhpMvc\Models\User;

class Signup extends BaseController
{
    public function newAction(): void
    {
        if (Auth::getUser()) {
            $this->redirect('/');
        };

        BaseView::renderTemplate('signup/new.html');
    }

    public function createAction(): void
    {
        $user = new User($_POST);

        if ($user->save()) {
            $user->sendActivationEmail();
            $this->redirect('/signup/success');
        } else {
            BaseView::renderTemplate('signup/new.html', ['user' => $user]);
        }
    }

    public function successAction(): void
    {
        BaseView::renderTemplate('signup/success.html');
    }

    public function activateAction(): void
    {
        $tokenValue = $this->routeParams['token'];

        if (User::activate($tokenValue)) {
            $this->redirect('/signup/activated');
        }

        $this->redirect('/signup/not-activated');
    }

    public function activatedAction(): void
    {
        BaseView::renderTemplate('signup/activated.html');
    }

    public function notActivatedAction(): void
    {
        BaseView::renderTemplate('signup/not_activated.html');
    }
}
