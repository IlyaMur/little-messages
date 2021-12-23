<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\Post;
use Ilyamur\PhpMvc\App\Models\Comment;
use Ilyamur\PhpMvc\App\Models\Hashtag;

class Hashtags extends \Ilyamur\PhpMvc\Core\Controller
{
    public function showAction()
    {
        $posts = Post::findPostsByHashtag(
            $this->routeParams['hashtag']
        );

        $comments = Comment::getLastComments();

        View::renderTemplate('hashtags/show.html', ['posts' => $posts, 'comments' => $comments]);
    }
}
