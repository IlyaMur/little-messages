<?php

namespace Ilyamur\PhpMvc\App\Controllers;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\Core\View;

class Profile extends \Ilyamur\PhpMvc\App\Controllers\Authenticated
{
    public function showAction()
    {
        View::renderTemplate('profile/show.html', [
            'user' => Auth::getUser()
        ]);
    }
}
