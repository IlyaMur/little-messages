<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App;

echo getenv('DB_HOST');
exit;

use \Mailjet\Resources;
use \Mailjet\Client;

class Mail
{
    public static function send(string $to, string $subject, string $text, string $html, string $name): void
    {
        $mj = new Client(getenv('MJ_APIKEY_PUBLIC'), getenv('MJ_APIKEY_PRIVATE'), true, ['version' => 'v3.1']);

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

        $response = $mj->post(Resources::$Email, ['body' => $body]);
    }
}
