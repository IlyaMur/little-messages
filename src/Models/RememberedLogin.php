<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Models;

use PDO;
use Ilyamur\PhpMvc\Service\Token;
use Ilyamur\PhpMvc\Models\User;

class RememberedLogin extends BaseModel
{
    public static function findByToken(string $token): RememberedLogin|false
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

    public function getUserByToken()
    {
        return User::findById((int) $this->user_id);
    }

    public function hasExpired(): bool
    {
        return strtotime($this->expires_at) < time();
    }

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
