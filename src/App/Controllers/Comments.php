<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\Flash;
use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\Post;
use Ilyamur\PhpMvc\App\Models\User;
use Ilyamur\PhpMvc\App\Models\Comment;

class Comments extends \Ilyamur\PhpMvc\Core\Controller
{
    const COMMENTS_PER_PAGE = 5;

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
                'comments' => Comment::getCommentsByPostId((int) $_POST['postId']),
                'captcha' => $this->generateCaptcha()
            ]
        );
    }

    public function indexAction()
    {
        $user = User::findById((int) $this->routeParams['id']);

        if (!$user) {
            $this->toRootWithWarning();
        }

        $currentPage = $this->routeParams['page'] ?? 1;
        $paging = ceil(Comment::getTotalCountByUserId((int) $user->id) / static::COMMENTS_PER_PAGE);

        $comments = Comment::getCommentsByUserId(
            userId: (int)$user->id,
            page: (int) $currentPage,
            limit: static::COMMENTS_PER_PAGE
        );

        View::renderTemplate(
            'comments/index.html',
            [
                'comments' => $comments,
                'pagination' =>
                [
                    'current' => $currentPage,
                    'paging' => $paging,
                ],
                'user' => $user
            ]
        );
    }
}
