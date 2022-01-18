<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Views\BaseView;

class Pages extends BaseController
{
    public function aboutAction()
    {
        BaseView::renderTemplate('pages/about.html');
    }
}
