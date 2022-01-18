<?php

namespace Ilyamur\PhpMvc\Service;

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
        return hash_hmac('sha256', $this->token, SECRET_KEY);
    }
}
