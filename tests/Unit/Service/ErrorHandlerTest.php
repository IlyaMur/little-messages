<?php

namespace Ilyamur\PhpMvc\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Ilyamur\PhpMvc\Service\ErrorHandler;

class ErrorHandlerTest extends TestCase
{
    public function testConvertErrorToExceptionIfEnabled(): void
    {
        $this->expectException(\ErrorException::class);

        ErrorHandler::errorHandler(0, 'test', 'testFile', 7);
    }

    public function testDoesNotConvertErrorToExceptionIfDisabled(): void
    {
        error_reporting(0);
        $this->assertNull(ErrorHandler::errorHandler(0, 'test', 'testFile', 7));
    }
}
