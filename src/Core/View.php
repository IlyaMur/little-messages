<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Core;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\Flash;
use Ilyamur\PhpMvc\Config\Config;
use Twig\Extra\String\StringExtension;

class View
{
    public static function getTemplate(string $template, array $args = [])
    {
        static $twig = null;

        if ($twig === null) {
            $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../src/App/Views');
            $twig = new \Twig\Environment($loader);
            $twig->addExtension(new StringExtension());
            $twig->addGlobal('currentUser', Auth::getUser());
            $twig->addGlobal('flashMessages', Flash::getMessages());
            $twig->addGlobal('APP_ROOT', Config::ROOT_URL);
        }

        return $twig->render(ucfirst($template) . '.twig', $args);
    }

    public static function renderTemplate(string $template, array $args = []): void
    {
        echo static::getTemplate($template, $args);
    }
}
