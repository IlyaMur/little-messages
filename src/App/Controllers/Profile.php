<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\Flash;
use Ilyamur\PhpMvc\Core\View;

class Profile extends \Ilyamur\PhpMvc\App\Controllers\Authenticated
{
    public function showAction()
    {
        View::renderTemplate('profile/show.html', [
            'user' => Auth::getUser()
        ]);
    }

    public function editAction()
    {
        View::renderTemplate('profile/edit.html', [
            'user' => Auth::getUser()
        ]);
    }

    public function update()
    {
        $user = Auth::getUser();

        if ($user->updateProfile($_POST)) {
            Flash::addMessage('Changes saved');
            $this->redirect('/profile/show');
        }

        View::renderTemplate('profile/edit.html', [
            'user' => $user
        ]);
    }
}
