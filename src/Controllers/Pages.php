<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Views\BaseView;

/**
 * Pages controller
 *
 * PHP version 8.0
 */
class Pages extends BaseController
{
    /**
     * Show about page
     * 
     * @return void
     */
    public function aboutAction()
    {
        BaseView::renderTemplate('pages/about.html');
    }
}
