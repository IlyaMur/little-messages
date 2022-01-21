<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Service\Flash;
use Ilyamur\PhpMvc\Views\BaseView;
use Ilyamur\PhpMvc\Models\Post;
use Ilyamur\PhpMvc\Models\User;
use Ilyamur\PhpMvc\Models\Comment;

/**
 * Comments controller
 *
 * PHP version 8.0
 */
class Comments extends BaseController
{
    /**
     * Comments rendering per page
     * @var int
     */
    public const COMMENTS_PER_PAGE = 5;

    /**
     * Creating new comment and rendering page with it
     *
     * @return void
     */
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

        BaseView::renderTemplate(
            'posts/show.html',
            [
                'comment' => $comment,
                'post' => $post,
                'comments' => Comment::getCommentsByPostId((int) $_POST['postId']),
                'captcha' => $this->generateCaptcha()
            ]
        );
    }

    /**
     * Rendering comments with pagination
     *
     * @return void
     */
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

        BaseView::renderTemplate(
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
