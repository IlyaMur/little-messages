<?php

namespace Ilyamur\PhpMvc\Service;

/**
 * Flash notification messages
 *
 * PHP version 8.0
 */
class Flash
{

    /**
     * Success message type
     * @var string
     */
    public const SUCCESS = 'success';

    /**
     * Information message type
     * @var string
     */
    public const INFO  = 'info';

    /**
     * Warning message type
     * @var string
     */
    public const WARNING  = 'warning';

    /**
     * Add a message
     *
     * @param string $message  The message content
     * @param string $type  The optional message type, defaults to SUCCESS
     *
     * @return void
     */
    public static function addMessage(string $msg, string $type = 'success'): void
    {
        // Create array in the session if it doesn't already exist
        $_SESSION['flashNotification'] = $_SESSION['flashNotification'] ?? [];

        // Append the message to the array
        // $_SESSION['flash_notifications'][] = $message;
        $_SESSION['flashNotification'][] = [
            'body' => $msg,
            'type' => $type
        ];
    }

    /**
     * Get all the messages
     *
     * @return mixed  An array with all the messages or null if none set
     */
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
