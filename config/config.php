<?php

declare(strict_types=1);

/**
 * Configuration file
 */

define('ROOT_URL', '/');

/**
 * Database SETTINGS
 */

define('DB_HOST', getenv('DB_HOST'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASSWORD', getenv('DB_PASSWORD'));

define('SECRET_KEY', 'dummykey');

/**
 * STORING SETTINGS
 */

// if false - img storing locally
define('AWS_STORING', false);

define('S3_REGION', getenv('S3_REGION'));
define('S3_URL', getenv('S3_URL'));
define('S3_ACCESS_KEY', getenv('S3_ACCESS_KEY'));
define('S3_SECRET_KEY', getenv('S3_SECRET_KEY'));
define('S3_BUCKET_NAME', getenv('S3_BUCKET_NAME'));

/**
 * EXCEPTIONS/ERRORS 
 */

// showing errors info
define('SHOW_ERRORS', true);

// logs directory
define('LOG_DIR', __DIR__ . '/../logs/' . date('Y-m-d') . '.txt');

// high level errors handlers
// error_reporting(E_ALL);
// set_error_handler('Ilyamur\PhpMvc\Service\ErrorHandler::errorHandler');
// set_exception_handler('Ilyamur\PhpMvc\Service\ErrorHandler::exceptionHandler');
