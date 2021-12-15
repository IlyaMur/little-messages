<?php

namespace Ilyamur\PhpMvc\App;

use Ilyamur\PhpMvc\Config\Config;

class Token
{
    protected string $token;

    public function __construct($tokenValue = null)
    {
        $this->token = $tokenValue ?? bin2hex(random_bytes(16));
    }

    public function getValue(): string
    {
        return $this->token;
    }

    public function getHash(): string
    {
        return hash_hmac('sha256', $this->token, Config::SECRET_KEY);
    }
}
