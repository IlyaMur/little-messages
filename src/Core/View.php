<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Core;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\Flash;

class View
{
    public static function renderTemplate(string $template, array $args = [])
    {
        static $twig = null;

        if ($twig === null) {
            $loader = new \Twig\Loader\FilesystemLoader('../src/App/Views');
            $twig = new \Twig\Environment($loader);
            $twig->addGlobal('currentUser', Auth::getUser());
            $twig->addGlobal('flashMessages', Flash::getMessages());
        }

        echo $twig->render($template . '.html.twig', $args);
    }
}
