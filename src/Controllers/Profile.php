<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Service\Auth;
use Ilyamur\PhpMvc\Service\Flash;
use Ilyamur\PhpMvc\Views\BaseView;
use Ilyamur\PhpMvc\Models\User;
use Ilyamur\PhpMvc\Models\Comment;

class Profile extends BaseController
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
        $userComments = Comment::getCommentsByUserId((int)$this->user->id);

        BaseView::renderTemplate('profile/show.html', [
            'user' => $this->user,
            'comments' => $userComments
        ]);
    }

    public function editAction(): void
    {
        if ($this->user->id !== Auth::getUser()?->id) {
            $this->toRootWithWarning('Access denied');
        }

        BaseView::renderTemplate('profile/edit.html', [
            'user' => $this->user
        ]);
    }

    public function updateAction(): void
    {
        if ($this->user->id !== Auth::getUser()?->id) {
            $this->toRootWithWarning('Access denied');
        }

        if ($this->user->update($_POST, $_FILES)) {
            Flash::addMessage('Changes saved');
            $this->redirect("/profile/{$this->user->id}");
        }

        BaseView::renderTemplate('profile/edit.html', [
            'user' => $this->user
        ]);
    }
}
