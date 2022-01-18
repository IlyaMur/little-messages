<?php

namespace Ilyamur\PhpMvc\Service;

class Flash
{
    public const SUCCESS = 'success';
    public const INFO  = 'info';
    public const WARNING  = 'warning';

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
        if (empty($_SESSION['flashNotification'])) {
            return null;
        }

        $messages = $_SESSION['flashNotification'];
        unset($_SESSION['flashNotification']);

        return $messages;
    }
}
