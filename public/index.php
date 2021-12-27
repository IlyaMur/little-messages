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

$router->add(route: '', params: ['controller' => 'posts', 'action' => 'index']);
$router->add(route: 'page/{page:[\d+]}', params: ['controller' => 'posts', 'action' => 'index']);
$router->add(route: 'posts/{action}/{id:\d+}', params: ['controller' => 'posts']);

$router->add(route: 'comments/{id:\d+}', params: ['controller' => 'comments', 'action' => 'index']);
$router->add(route: 'comments/{id:\d+}/page/{page:\d+}', params: ['controller' => 'comments', 'action' => 'index']);

$router->add(route: 'login', params: ['controller' => 'login', 'action' => 'new']);
$router->add(route: 'logout', params: ['controller' => 'login', 'action' => 'destroy']);
$router->add(route: 'signup/activate/{token:[\da-f]+}', params: ['controller' => 'signup', 'action' => 'activate']);
$router->add(route: 'password/reset/{token:[\da-f]+}', params: ['controller' => 'password', 'action' => 'reset']);

$router->add(route: 'profile/{id:[\da-f]+}', params: ['controller' => 'profile', 'action' => 'show']);
$router->add(route: 'profile/{action}/{id:[\da-f]+}', params: ['controller' => 'profile']);

$router->add(route: 'hashtags/{action}/{hashtag:[а-яa-z]+}', params: ['controller' => 'hashtags']);

$router->add(route: '{controller}/{action}');
$router->add(route: '{controller}/{id:\d+}/{action}');

$router->add(route: 'admin/{controller}/{action}', params: ['namespace' => 'Admin']);

$router->dispatch($_SERVER['QUERY_STRING']);
