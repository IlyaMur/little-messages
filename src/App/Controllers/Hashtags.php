<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\Post;
use Ilyamur\PhpMvc\App\Models\Comment;

class Hashtags extends \Ilyamur\PhpMvc\Core\Controller
{
    public function showAction()
    {
        View::renderTemplate(
            'hashtags/show.html',
            [
                'hashtag' => $this->routeParams['hashtag'],
                'posts' => Post::findPostsByHashtag($this->routeParams['hashtag']),
                'comments' => Comment::getLastComments(),
            ]
        );
    }
}
