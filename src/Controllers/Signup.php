<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Service\Auth;
use Ilyamur\PhpMvc\Views\BaseView;
use Ilyamur\PhpMvc\Models\User;

/**
 * Signup controller
 *
 * PHP version 8.0
 */
class Signup extends BaseController
{
    /**
     * Show the signup page
     *
     * @return void
     */
    public function newAction(): void
    {
        if (Auth::getUser()) {
            $this->redirect('/');
        };

        BaseView::renderTemplate('signup/new.html');
    }

    /**
     * Sign up a new user
     *
     * @return void
     */
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

    /**
     * Show the signup success page
     *
     * @return void
     */
    public function successAction(): void
    {
        BaseView::renderTemplate('signup/success.html');
    }

    /**
     * Activate a new account
     *
     * @return void
     */
    public function activateAction(): void
    {
        $tokenValue = $this->routeParams['token'];

        if (User::activate($tokenValue)) {
            $this->redirect('/signup/activated');
        }

        $this->redirect('/signup/not-activated');
    }

    /**
     * Show the activation success page
     *
     * @return void
     */
    public function activatedAction(): void
    {
        BaseView::renderTemplate('signup/activated.html');
    }

    /**
     * Show the incorrect activation page
     *
     * @return void
     */
    public function notActivatedAction(): void
    {
        BaseView::renderTemplate('signup/not_activated.html');
    }
}
