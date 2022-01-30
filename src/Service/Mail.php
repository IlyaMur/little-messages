<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Service;

use Mailjet\Resources;
use Mailjet\Client;

/**
 * Mail
 *
 * PHP version 8.0
 */
class Mail
{
    /**
     * Send a message
     *
     * @param string $to Recipient
     * @param string $subject Subject
     * @param string $text Text-only content of the message
     * @param string $html HTML content of the message
     * @param string $text Recipient name
     *
     * @return void
     */
    public static function send(string $to, string $subject, string $text, string $html, string $name): void
    {
        $mj = new Client($_ENV['MJ_APIKEY_PUBLIC'], $_ENV['MJ_APIKEY_PRIVATE'], true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "ilyamur@hotmail.com",
                        'Name' => "myPosts App"
                    ],
                    'To' => [
                        [
                            'Email' => $to,
                            'Name' => $name
                        ]
                    ],
                    'Subject' => $subject,
                    'TextPart' => $text,
                    'HTMLPart' => $html
                ]
            ]
        ];
        $mj->post(Resources::$Email, ['body' => $body]);
    }
}
