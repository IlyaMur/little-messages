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

        $data = [
            'posts' => $posts
        ];

        View::renderTemplate('posts/index.html', $data);
    }
}
