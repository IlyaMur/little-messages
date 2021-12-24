<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\Flash;
use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\Post;
use Ilyamur\PhpMvc\App\Models\Comment;

class Comments extends \Ilyamur\PhpMvc\Core\Controller
{
    public function createAction()
    {
        $comment = new Comment($_POST);

        $post = Post::findById((int) $_POST['postId']);



        if (!$post) {
            $this->toRootWithWarning();
        }

        if ($comment->save($this->getCaptcha())) {
            Flash::addMessage('Comment Added', Flash::SUCCESS);
            $this->redirect("/posts/show/$post->id");
        }

        View::renderTemplate(
            'posts/show.html',
            [
                'comment' => $comment,
                'post' => $post,
                'comments' => Comment::getCommentsById((int) $_POST['postId']),
                'captcha' => $this->generateCaptcha()
            ]
        );
    }
}
