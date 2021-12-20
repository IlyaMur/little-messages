<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\Post;

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
        View::renderTemplate('posts/new.html');
    }

    public function createAction()
    {
        View::renderTemplate('posts/new.html');
    }
}
