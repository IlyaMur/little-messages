<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\Flash;
use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\Post;
use Ilyamur\PhpMvc\App\Models\Comment;
use Ilyamur\PhpMvc\App\Models\Hashtag;


class Posts extends \Ilyamur\PhpMvc\Core\Controller
{
    const POST_PER_PAGE = 3;

    public function indexAction(): void
    {
        $currentPage = $this->routeParams['page'] ?? 1;
        $paging = ceil(Post::getTotalCount() / static::POST_PER_PAGE);

        View::renderTemplate(
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

        View::renderTemplate('posts/new.html');
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
        View::renderTemplate('posts/new.html', ['post' => $post]);
    }

    public function showAction(): void
    {
        $post = Post::findById(
            (int) $this->routeParams['id']
        );

        if (!$post) {
            $this->toRootWithWarning();
        }

        $postComments = Comment::getCommentsById(
            (int) $this->routeParams['id']
        );

        View::renderTemplate(
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

        $post->body = htmlspecialchars_decode($post->body);
        $post->body = strip_tags($post->body);

        if (!$post || $post->user_id !== Auth::getUser()->id) {
            Flash::addMessage('You can\'t edit this post', Flash::WARNING);
            $this->redirect('/');
        }

        View::renderTemplate('posts/edit.html', ['post' => $post]);
    }

    public function updateAction(): void
    {
        if (!Auth::getUser()) {
            $this->requireLogin();
        }

        $post = Post::findById((int) $this->routeParams['id']);

        if (!$post || $post->user_id !== Auth::getUser()->id) {
            Flash::addMessage('You can\'t edit this post', Flash::WARNING);
            $this->redirect('/');
        }

        if ($post->update($_POST, $_FILES)) {
            Flash::addMessage('Changes saved');
            $this->redirect("/posts/show/$post->id");
        }

        View::renderTemplate('posts/edit.html', ['post' => $post]);
    }

    public function destroy()
    {
        $post = Post::findById(
            (int) $this->routeParams['id']
        );

        if (!$post || $post->user_id !== Auth::getUser()->id) {
            Flash::addMessage('You can\'t delete this post', Flash::WARNING);
            $this->redirect("/posts/show/$post->id");
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
