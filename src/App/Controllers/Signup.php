<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\User;

class Signup extends \Ilyamur\PhpMvc\Core\Controller
{
    public function newAction(): void
    {
        View::renderTemplate('Signup/new');
    }

    public function createAction(): void
    {
        $user = new User($_POST);

        if ($user->save()) {
            header('location: http://' . $_SERVER['HTTP_HOST'] . '/signup/success', true, 303);
            exit;
        } else {
            View::renderTemplate('Signup/new', ['user' => $user]);
        }
    }

    public function successAction(): void
    {
        View::renderTemplate('Signup/success');
    }
}
