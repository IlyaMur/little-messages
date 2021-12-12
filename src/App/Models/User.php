<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Models;

use PDO;

class User extends \Ilyamur\PhpMvc\Core\Model
{
    public array $errors = [];

    public function __construct(array $data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    public function save()
    {
        $passwordHash = password_hash($this->password, PASSWORD_DEFAULT);
        $sql = 'INSERT INTO users (name, email, password_hash)
                VALUES (:name, :email, :password_hash)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
        $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
        $stmt->bindValue(':password_hash', $passwordHash, PDO::PARAM_STR);

        $stmt->execute();
    }
}
