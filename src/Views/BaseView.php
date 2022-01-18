<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Views;

use Ilyamur\PhpMvc\Service\Auth;
use Ilyamur\PhpMvc\Service\Flash;
use Twig\Extra\String\StringExtension;

class BaseView
{
    public static function getTemplate(string $template, array $args = [])
    {
        static $twig = null;

        if ($twig === null) {
            $loader = new \Twig\Loader\FilesystemLoader(__DIR__);
            $twig = new \Twig\Environment($loader);
            $twig->addExtension(new StringExtension());
            $twig->addGlobal('currentUser', Auth::getUser());
            $twig->addGlobal('flashMessages', Flash::getMessages());
            $twig->addGlobal('APP_ROOT', ROOT_URL);
        }

        return $twig->render(ucfirst($template) . '.twig', $args);
    }

    public static function renderTemplate(string $template, array $args = []): void
    {
        echo static::getTemplate($template, $args);
    }
}
