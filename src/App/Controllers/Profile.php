<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\Flash;
use Ilyamur\PhpMvc\Core\View;

class Profile extends Authenticated
{
    protected function before()
    {
        parent::before();

        $this->user = Auth::getUser();
    }

    public function showAction(): void
    {
        View::renderTemplate('profile/show.html', [
            'user' => $this->user
        ]);
    }

    public function editAction(): void
    {
        View::renderTemplate('profile/edit.html', [
            'user' => $this->user
        ]);
    }

    public function updateAction(): void
    {
        if ($this->user->updateProfile($_POST)) {
            Flash::addMessage('Changes saved');
            $this->redirect('/profile/show');
        }

        View::renderTemplate('profile/edit.html', [
            'user' => $this->user
        ]);
    }
}
