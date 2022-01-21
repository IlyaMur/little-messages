<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Service\Auth;
use Ilyamur\PhpMvc\Service\Flash;
use Ilyamur\PhpMvc\Views\BaseView;
use Ilyamur\PhpMvc\Models\User;
use Ilyamur\PhpMvc\Models\Comment;

/**
 * Profile controller
 *
 * PHP version 8.0
 */
class Profile extends BaseController
{
    /**
     * Before filter - called before each action method
     *
     * @return void
     */
    protected function before(): void
    {
        $this->user = User::findById((int) $this->routeParams['id']);

        if (!$this->user) {
            $this->toRootWithWarning();
        }
    }

    /**
     * Show the profile
     *
     * @return void
     */
    public function showAction(): void
    {
        $userComments = Comment::getCommentsByUserId((int)$this->user->id);

        BaseView::renderTemplate('profile/show.html', [
            'user' => $this->user,
            'comments' => $userComments
        ]);
    }

    /**
     * Show the form for editing the profile
     *
     * @return void
     */
    public function editAction(): void
    {
        if ($this->user->id !== Auth::getUser()?->id) {
            $this->toRootWithWarning('Access denied');
        }

        BaseView::renderTemplate('profile/edit.html', [
            'user' => $this->user
        ]);
    }

    /**
     * Update the profile
     *
     * @return void
     */
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
