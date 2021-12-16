<?php

declare(strict_types=1);

/**
 * Front Controller
 * 
 * PHP version 8.0
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

error_reporting(E_ALL);
set_error_handler('Ilyamur\PhpMvc\Core\Error::errorHandler');
set_exception_handler('Ilyamur\PhpMvc\Core\Error::exceptionHandler');

session_start();

/**
 * Routing
 */

$router = new Ilyamur\PhpMvc\Core\Router();

$router->add(route: '', params: ['controller' => 'Home', 'action' => 'index']);
$router->add(route: 'login', params: ['controller' => 'Login', 'action' => 'new']);
$router->add(route: 'logout', params: ['controller' => 'Login', 'action' => 'destroy']);

$router->add(route: '{controller}/{action}');
$router->add(route: '{controller}/{id:\d+}/{action}');
$router->add(route: 'admin/{controller}/{action}', params: ['namespace' => 'Admin']);
$router->add(route: 'password/reset/{token:[\da-f]+}', params: ['controller' => 'password', 'action' => 'reset']);


$router->dispatch($_SERVER['QUERY_STRING']);
