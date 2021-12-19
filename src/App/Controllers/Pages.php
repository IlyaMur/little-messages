<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\Config\Config;

class Pages extends \Ilyamur\PhpMvc\Core\Controller
{
    public function indexAction()
    {
        $data = [
            'title' => 'MyPosts',
            'description' =>  'Simple social network built on the "PHP On Rails" framework'
        ];

        View::renderTemplate('pages/index.html', $data);
    }

    public function aboutAction()
    {
        $data = [
            'title' => 'About Us',
            'description' =>  'myPosts is an app to share posts with another user!',
            'appVersion' => Config::APP_VERSION
        ];

        View::renderTemplate('pages/about.html', $data);
    }
}
