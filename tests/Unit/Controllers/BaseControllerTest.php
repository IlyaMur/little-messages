<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use Ilyamur\PhpMvc\Tests\Unit\Controllers\TestDoubles\BaseControllerChild;

class BaseControllerTest extends TestCase
{
    public function testThrowAnExceptionWhenMethodNotFound(): void
    {
        $this->controller = new BaseControllerChild([]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Method NotExistAction not found in controller" . $this->controller::class);

        $this->controller->NotExist();
    }

    public function testCallCorrectMethodWhenItExists(): void
    {
        $mock = $this->getMockBuilder(BaseControllerChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['testMethodAction'])
            ->getMock();

        $mock->expects($this->once())->method('testMethodAction');

        $mock->testMethod();
    }

    public function testCallBeforeMethod(): void
    {
        $mock = $this->getMockBuilder(BaseControllerChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['before'])
            ->getMock();
        $mock->expects($this->once())->method('before');

        $mock->testMethod();
    }

    public function testCallAfterIfBeforeReturnsTrue(): void
    {
        $mock = $this->getMockBuilder(BaseControllerChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['after', 'before'])
            ->getMock();

        $mock->expects($this->once())->method('before')->willReturn(true);
        $mock->expects($this->once())->method('after');

        $mock->testMethod();
    }

    public function testDoNotCallAfterIfBeforeReturnsFalse(): void
    {
        $mock = $this->getMockBuilder(BaseControllerChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['after', 'before'])
            ->getMock();

        $mock->expects($this->once())->method('before')->willReturn(false);
        $mock->expects($this->never())->method('after');

        $mock->testMethod();
    }

    public function testDoNotCallControllerActionIfBeforeReturnsFalse(): void
    {
        $mock = $this->getMockBuilder(BaseControllerChild::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['testMethodAction', 'before'])
            ->getMock();

        $mock->expects($this->once())->method('before')->willReturn(false);
        $mock->expects($this->never())->method('testMethodAction');

        $mock->testMethod();
    }
}
