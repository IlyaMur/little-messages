<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Models;

use PDO;
use Ilyamur\PhpMvc\App\Token;
use Ilyamur\PhpMvc\App\Models\User;

class RememberedLogin extends \Ilyamur\PhpMvc\Core\Model
{
    public static function findByToken(string $token): ?RememberedLogin
    {
        $token = new Token($token);
        $hashedToken = $token->getHash();

        $sql = 'SELECT * FROM remembered_logins
                WHERE token_hash = :hashedToken';

        $db = static::getDB();

        $stmt = $db->prepare($sql);
        $stmt->bindValue('hashedToken', $hashedToken, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        $rememberedLogin = $stmt->fetch();

        return $rememberedLogin ? $rememberedLogin : null;
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
