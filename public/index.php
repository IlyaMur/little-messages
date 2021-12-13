<?php

declare(strict_types=1);
error_reporting(E_ALL);

/**
 * Front Controller
 * 
 * PHP version 8.0
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

set_error_handler('Ilyamur\PhpMvc\Core\Error::errorHandler');
set_exception_handler('Ilyamur\PhpMvc\Core\Error::exceptionHandler');

/**
 * Routing
 */

$router = new Ilyamur\PhpMvc\Core\Router();

$router->add(route: '', params: ['controller' => 'Home', 'action' => 'index']);
$router->add(route: 'login', params: ['controller' => 'Login', 'action' => 'new']);

$router->add(route: '{controller}/{action}');
$router->add(route: '{controller}/{id:\d+}/{action}');
$router->add(route: 'admin/{controller}/{action}', params: ['namespace' => 'Admin']);

$router->dispatch($_SERVER['QUERY_STRING']);
