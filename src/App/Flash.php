<?php

namespace Ilyamur\PhpMvc\App;

class Flash
{
    const SUCCESS = 'success';
    const INFO  = 'info';
    const WARNING  = 'warning';

    public static function addMessage(string $msg, string $type = 'success'): void
    {
        $_SESSION['flashNotification'] = $_SESSION['flashNotification'] ?? [];

        $_SESSION['flashNotification'][] = [
            'body' => $msg,
            'type' => $type
        ];
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
