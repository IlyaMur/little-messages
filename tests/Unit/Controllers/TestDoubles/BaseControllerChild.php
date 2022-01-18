<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Tests\Unit\Controllers\TestDoubles;

use Ilyamur\PhpMvc\Controllers\BaseController;

class BaseControllerChild extends BaseController
{
    public function testMethodAction()
    {
        echo 'testMethod called';
    }

    public function before()
    {
    }

    public function after()
    {
    }
}
