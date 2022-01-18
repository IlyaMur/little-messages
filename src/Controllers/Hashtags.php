<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Views\BaseView;
use Ilyamur\PhpMvc\Models\Post;
use Ilyamur\PhpMvc\Models\Comment;

class Hashtags extends BaseController
{
    public function showAction()
    {
        BaseView::renderTemplate(
            'hashtags/show.html',
            [
                'hashtag' => $this->routeParams['hashtag'],
                'posts' => Post::findPostsByHashtag($this->routeParams['hashtag']),
                'comments' => Comment::getLastComments(),
            ]
        );
    }
}
