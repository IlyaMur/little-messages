<?php

namespace Ilyamur\PhpMvc\Service;

/**
 * Unique random tokens
 *
 * PHP version 8.0
 */
class Token
{
    /**
     * The token value
     * @var string
     */
    protected string $token;

    /**
     * Class constructor. Create a new random token or assign an existing one if passed in.
     *
     * @param string $tokenValue (optional) A token value
     *
     * @return void
     */
    public function __construct($tokenValue = null)
    {
        $this->token = $tokenValue ?? bin2hex(random_bytes(16));
    }

    /**
     * Get the token value
     *
     * @return string The value
     */
    public function getValue(): string
    {
        return $this->token;
    }

    /**
     * Get the hashed token value
     *
     * @return string The hashed value
     */
    public function getHash(): string
    {
        return hash_hmac('sha256', $this->token, SECRET_KEY);
    }
}
