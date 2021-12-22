<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\Flash;
use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\Post;

class Comments extends \Ilyamur\PhpMvc\Core\Controller
{
    public function createAction()
    {
        if (!Auth::getUser()) {
            $this->requireLogin();
        }

        $comment = new Comment($_POST);

        if ($comment->save()) {
            Flash::addMessage('Comment Added', Flash::SUCCESS);
            $this->redirect('/');
        }

        View::renderTemplate('posts/show.html', ['comment' => $comment]);
    }
}
