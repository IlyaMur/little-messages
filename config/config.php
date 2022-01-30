<?php

declare(strict_types=1);

use Dotenv\Dotenv;

/**
 * Configuration file
 */

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

define('ROOT_URL', '/');

/**
 * Database settings
 */

define('DB_USER', $_ENV['MYSQL_USER']);
define('DB_PASSWORD',  $_ENV['MYSQL_PASSWORD']);
define('DB_HOST', $_ENV['MYSQL_HOST']);
define('DB_NAME',  $_ENV['MYSQL_DATABASE']);

// Key for tokens hashing
define('SECRET_KEY', $_ENV['SECRET_KEY']);

/**
 * Storing settings
 */

// If false - images storing locally
define('AWS_STORING', false);

define('S3_REGION',  $_ENV['S3_REGION']);
define('S3_URL',  $_ENV['S3_URL']);
define('S3_ACCESS_KEY',  $_ENV['S3_ACCESS_KEY']);
define('S3_SECRET_KEY',  $_ENV['S3_SECRET_KEY']);
define('S3_BUCKET_NAME',  $_ENV['S3_BUCKET_NAME']);

/**
 * Exceptions/Errors 
 */

// Showing errors info
define('SHOW_ERRORS', true);

// Logs directory
define('LOG_DIR', __DIR__ . '/../logs/' . date('Y-m-d') . '.txt');

// High level errors handlers
error_reporting(E_ALL);
set_error_handler('Ilyamur\PhpMvc\Service\ErrorHandler::errorHandler');
set_exception_handler('Ilyamur\PhpMvc\Service\ErrorHandler::exceptionHandler');
