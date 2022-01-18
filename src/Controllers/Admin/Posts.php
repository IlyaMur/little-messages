<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers\Admin;

use Ilyamur\PhpMvc\Service\Flash;
use Ilyamur\PhpMvc\Views\BaseView;
use Ilyamur\PhpMvc\Models\Post;

class Posts extends \Ilyamur\PhpMvc\Controllers\BaseController
{
    protected function before(): void
    {
        if (!$this->isAdmin()) {
            $this->toRootWithWarning('Access denied');
        }
    }

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
