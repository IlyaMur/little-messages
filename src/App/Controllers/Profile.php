<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\Flash;
use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\User;

class Profile extends \Ilyamur\PhpMvc\Core\Controller
{
    protected function before(): void
    {
        $this->user = User::findById((int) $this->routeParams['id']);

        if (!$this->user) {
            $this->toRootWithWarning();
        }
    }

    public function showAction(): void
    {
        View::renderTemplate('profile/show.html', [
            'user' => $this->user
        ]);
    }

    public function editAction(): void
    {
        if ($this->user->id !== Auth::getUser()->id) {
            $this->toRootWithWarning('Access denied');
        }

        View::renderTemplate('profile/edit.html', [
            'user' => $this->user
        ]);
    }

    public function updateAction(): void
    {
        if ($this->user->id !== Auth::getUser()->id) {
            $this->toRootWithWarning('Access denied');
        }

        if ($this->user->update($_POST, $_FILES)) {
            Flash::addMessage('Changes saved');
            $this->redirect("/profile/{$this->user->id}");
        }

        View::renderTemplate('profile/edit.html', [
            'user' => $this->user
        ]);
    }
}
