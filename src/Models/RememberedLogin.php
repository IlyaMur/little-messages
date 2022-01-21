<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Models;

use PDO;
use Ilyamur\PhpMvc\Service\Token;
use Ilyamur\PhpMvc\Models\User;

/**
 * Remembered login model
 *
 * PHP version 8.0
 */
class RememberedLogin extends BaseModel
{
    /**
     * Find a remembered login model by the token
     *
     * @param string $token The remembered login token
     *
     * @return mixed Remembered login object if found, false otherwise
     */
    public static function findByToken(string $token): RememberedLogin | false
    {
        $token = new Token($token);
        $hashedToken = $token->getHash();

        $sql = 'SELECT * FROM remembered_logins
                WHERE token_hash = :hashedToken';

        $db = static::getDB();

        $stmt = $db->prepare($sql);
        $stmt->bindValue('hashedToken', $hashedToken, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, static::class);

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get the user model associated with this remembered login
     *
     * @return User The user model
     */
    public function getUserByToken()
    {
        return User::findById((int) $this->user_id);
    }

    /**
     * See if the remember token has expired or not, based on the current system time
     *
     * @return boolean True if the token has expired, false otherwise
     */
    public function hasExpired(): bool
    {
        return strtotime($this->expires_at) < time();
    }

    /**
     * Delete this model
     *
     * @return void
     */
    public function delete(): void
    {
        $sql = 'DELETE FROM remembered_logins 
                WHERE token_hash = :tokenHash';

        $db = static::getDB();

        $stmt = $db->prepare($sql);
        $stmt->bindValue('tokenHash', $this->token_hash, PDO::PARAM_STR);
        $stmt->execute();
    }
}
