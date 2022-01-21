<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers\Admin;

use Ilyamur\PhpMvc\Service\Flash;
use Ilyamur\PhpMvc\Views\BaseView;
use Ilyamur\PhpMvc\Models\Post;

/**
 * Posts admin controller
 *
 * PHP version 8.0
 */
class Posts extends \Ilyamur\PhpMvc\Controllers\BaseController
{
    /**
     * Require the user to be admin before giving access to all methods in the controller
     *
     * @return void
     */
    protected function before(): void
    {
        if (!$this->isAdmin()) {
            $this->toRootWithWarning('Access denied');
        }
    }

    /**
     * Render all posts from the blog
     *
     * @return void
     */
    public function indexAction(): void
    {
        $posts = Post::getPosts(limit: 100);
        BaseView::renderTemplate(
            'Admin/index.html',
            [
                'posts' => $posts,
            ]
        );
    }

    /**
     * Deleting a post and rendering Flash message
     *
     * @return void
     */
    public function destroyAction()
    {
        $post = Post::findById(
            (int) $this->routeParams['id']
        );

        if (!$post) {
            $this->toRootWithWarning('You can\'t delete this post');
        }

        if ($post->delete()) {
            Flash::addMessage('Post was deleted');
            $this->redirect('/admin/posts/index');
        } else {
            Flash::addMessage('Something went wrong', Flash::WARNING);
            $this->redirect("/admin/posts/index");
        }
    }
}
