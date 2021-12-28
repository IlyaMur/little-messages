<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers\Admin;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\Flash;
use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\Models\Post;
use Ilyamur\PhpMvc\App\Models\Comment;
use Ilyamur\PhpMvc\App\Models\Hashtag;


class Posts extends \Ilyamur\PhpMvc\Core\Controller
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
        View::renderTemplate(
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
