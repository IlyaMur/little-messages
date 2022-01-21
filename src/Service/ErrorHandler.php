<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Service;

use Ilyamur\PhpMvc\Views\BaseView;

/**
 * Error and exception handler
 *
 * PHP version 8.0
 */
class ErrorHandler
{
    /**
     * Error handler. Convert all errors to Exceptions by throwing an ErrorException.
     *
     * @param int $level  Error level
     * @param string $message  Error message
     * @param string $file  Filename the error was raised in
     * @param int $line  Line number in the file
     *
     * @return void
     */
    public static function errorHandler(int $level, string $message, string $file, int $line)
    {
        if (error_reporting() !== 0) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Exception handler.
     * Selecting an exception output. Log or render to the screen.
     *
     * @param Exception $exception  The exception
     *
     * @return void
     */
    public static function exceptionHandler(\Throwable $exception)
    {
        // Code is 404 (not found) or 500 (general error)
        $code = $exception->getCode();
        if ($code != 404) {
            $code = 500;
        }

        http_response_code($code);

        if (SHOW_ERRORS) {
            echo "<h1>Fatal error</h1>";
            echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            echo "<p>Message: '" . $exception->getMessage() . "'</p>";
            echo "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
            echo "<p>Thrown in '" . $exception->getFile() . "' on line " .
                $exception->getLine() . "</p>";
        } else {
            ini_set('error_log', LOG_DIR);

            $message = "<h1>Fatal error</h1>";
            $message .= "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            $message .= "<p>Message: '" . $exception->getMessage() . "'</p>";
            $message .= "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
            $message .= "<p>Thrown in '" . $exception->getFile() . "' on line " .
                $exception->getLine() . "</p>";

            error_log($message);

            BaseView::renderTemplate("$code" . '.html');
        }
    }
}
