<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\Flash;
use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\Post;
use Ilyamur\PhpMvc\App\Models\User;

class Posts extends \Ilyamur\PhpMvc\Core\Controller
{
    public function indexAction(): void
    {
        $posts = Post::getPosts();

        View::renderTemplate('posts/index.html', [
            'posts' => $posts
        ]);
    }

    public function newAction(): void
    {
        if (!Auth::getUser()) {
            Flash::addMessage('You need to login for posting', Flash::INFO);
            $this->redirect('/');
        }

        View::renderTemplate('posts/new.html');
    }

    public function createAction()
    {
        $post = new Post($_POST);

        if ($post->save()) {
            Flash::addMessage('Post Added', Flash::SUCCESS);
            $this->redirect('/');
        }

        View::renderTemplate('posts/new.html', ['post' => $post]);
    }

    public function show(): void
    {
        $post = Post::findById(
            (int) $this->routeParams['id']
        );

        if (!$post) {
            Flash::addMessage('Nothing found', Flash::WARNING);
            $this->redirect('/');
        }

        $user = User::findById(
            (int) $post->user_id
        );

        View::renderTemplate('posts/show.html', ['post' => $post, 'user' => $user]);
    }
}
