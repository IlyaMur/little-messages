<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Views\BaseView;
use Ilyamur\PhpMvc\Models\Post;
use Ilyamur\PhpMvc\Models\Comment;

/**
 * Hashtags controller
 *
 * PHP version 8.0
 */
class Hashtags extends BaseController
{
    /**
     * Show posts with specific hashtag
     *
     * @return void
     */
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
