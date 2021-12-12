<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Core;

use Ilyamur\PhpMvc\Config\Config;
use Ilyamur\PhpMvc\Core\View;

class Error
{
    public static function errorHandler(int $level, string $message, string $file, int $line)
    {
        if (error_reporting() !== 0) {
            throw new \ErrorException($message,  0,  $level,  $file,  $line);
        }
    }

    public static function exceptionHandler(\Throwable $exception)
    {
        $code = $exception->getCode();

        if ($code != 404) {
            $code = 500;
        }

        http_response_code($code);

        if (Config::SHOW_ERRORS) {
            echo "<h1>Fatal error</h1>";
            echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            echo "<p>Message: '" . $exception->getMessage() . "'</p>";
            echo "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
            echo "<p>Thrown in '" . $exception->getFile() . "' on line " .
                $exception->getLine() . "</p>";
        } else {
            $log = dirname(__DIR__) . '/../logs/' . date('Y-m-d') . '.txt';
            ini_set('error_log', $log);

            $message = "<h1>Fatal error</h1>";
            $message .= "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            $message .= "<p>Message: '" . $exception->getMessage() . "'</p>";
            $message .= "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
            $message .= "<p>Thrown in '" . $exception->getFile() . "' on line " .
                $exception->getLine() . "</p>";

            error_log($message);

            View::renderTemplate("$code");
        }
    }
}
