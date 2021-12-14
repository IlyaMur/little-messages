<?php

namespace Ilyamur\PhpMvc\App;

class Flash
{
    public static function addMessage(string $msg): void
    {
        $_SESSION['flashNotification'] = $_SESSION['flashNotification'] ?? [];

        $_SESSION['flashNotification'][] = $msg;
    }

    public static function getMessages(): ?array
    {
        if (!isset($_SESSION['flashNotification'])) {
            return null;
        }

        $messages = $_SESSION['flashNotification'];
        unset($_SESSION['flashNotification']);

        return $messages;
    }
}
