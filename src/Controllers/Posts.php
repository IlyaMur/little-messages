<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Service\Auth;
use Ilyamur\PhpMvc\Service\Flash;
use Ilyamur\PhpMvc\Views\BaseView;
use Ilyamur\PhpMvc\Models\Post;
use Ilyamur\PhpMvc\Models\Comment;
use Ilyamur\PhpMvc\Models\Hashtag;

class Posts extends BaseController
{
    public const POST_PER_PAGE = 3;

    public function indexAction(): void
    {
        $currentPage = $this->routeParams['page'] ?? 1;
        $paging = ceil(Post::getTotalCount() / static::POST_PER_PAGE);

        BaseView::renderTemplate(
            'posts/index.html',
            [
                'posts' => Post::getPosts(page: (int) $currentPage, limit: static::POST_PER_PAGE),
                'pagination' =>
                [
                    'current' => $currentPage,
                    'paging' => $paging,
                ],
                'comments' => Comment::getLastComments(),
                'hashtags' => Hashtag::getLastActualHashtags()
            ]
        );
    }

    public function newAction(): void
    {
        if (!Auth::getUser()) {
            $this->requireLogin();
        }

        BaseView::renderTemplate('posts/new.html');
    }

    public function createAction()
    {
        if (!Auth::getUser()) {
            $this->requireLogin();
        }

        $post = new Post($_POST, $_FILES);

        if ($post->save()) {
            Flash::addMessage('Post Added', Flash::SUCCESS);
            $this->redirect('/');
        }

        Flash::addMessage('Posts not added', Flash::WARNING);
        BaseView::renderTemplate('posts/new.html', ['post' => $post]);
    }

    public function showAction(): void
    {
        $post = Post::findById(
            (int) $this->routeParams['id']
        );

        if (!$post) {
            $this->toRootWithWarning();
        }

        $postComments = Comment::getCommentsByPostId(
            (int) $this->routeParams['id']
        );

        BaseView::renderTemplate(
            'posts/show.html',
            [
                'post' => $post,
                'comments' => $postComments,
                'captcha' => $this->generateCaptcha()
            ]
        );
    }

    public function editAction(): void
    {
        if (!Auth::getUser()) {
            $this->requireLogin();
        }

        $post = Post::findById(
            (int) $this->routeParams['id']
        );

        if (!$post || $post->user_id !== Auth::getUser()->id) {
            Flash::addMessage('You can\'t edit this post', Flash::WARNING);
            $this->redirect('/');
        }

        $post->body = strip_tags($post->body);

        BaseView::renderTemplate('posts/edit.html', ['post' => $post]);
    }

    public function updateAction(): void
    {
        if (!Auth::getUser()) {
            $this->requireLogin();
        }

        $post = Post::findById((int) $this->routeParams['id']);

        if (!$post || $post->user_id !== Auth::getUser()?->id) {
            Flash::addMessage('You can\'t edit this post', Flash::WARNING);
            $this->redirect('/');
        }

        if ($post->update($_POST, $_FILES)) {
            Flash::addMessage('Changes saved');
            $this->redirect("/posts/show/$post->id");
        }

        BaseView::renderTemplate('posts/edit.html', ['post' => $post]);
    }

    public function destroyAction()
    {
        $post = Post::findById(
            (int) $this->routeParams['id']
        );

        if (!$post || $post->user_id !== Auth::getUser()?->id) {
            $this->toRootWithWarning("You can't delete this post");
        }

        if ($post->delete()) {
            Flash::addMessage('Post was deleted');
            $this->redirect('/');
        } else {
            Flash::addMessage('Something went wrong', Flash::WARNING);
            $this->redirect("/posts/show/$post->id");
        }
    }
}
