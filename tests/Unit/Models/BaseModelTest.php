<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use Ilyamur\PhpMvc\Tests\Unit\Models\TestDoubles\BaseModelChild;

class BaseModelTest extends TestCase
{
    public function testReturnsPDOObject()
    {
        $this->assertInstanceOf('PDO', BaseModelChild::getDB());
    }
}
