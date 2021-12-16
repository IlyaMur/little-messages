<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App;

use \Mailjet\Resources;
use \Mailjet\Client;

class Mail
{
    public static function send(string $to, string $subject, string $text, string $html, string $name): void
    {
        $apikey = getenv('MJ_APIKEY_PUBLIC');
        $apisecret = getenv('MJ_APIKEY_PRIVATE');

        $mj = new Client(getenv('MJ_APIKEY_PUBLIC'), getenv('MJ_APIKEY_PRIVATE'), true, ['version' => 'v3.1']);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "ilyamur@hotmail.com",
                        'Name' => "Me"
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
