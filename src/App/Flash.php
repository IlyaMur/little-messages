<?php

namespace Ilyamur\PhpMvc\App;

class Flash
{
    public static function addMessage(string $msg): void
    {
        $_SESSION['flashNotification'] = $_SESSION['flashNotification'] ?? [];

        $_SESSION['flashNotification'][] = $msg;
    }
}
