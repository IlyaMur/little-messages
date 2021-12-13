<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Models;

use PDO;

class User extends \Ilyamur\PhpMvc\Core\Model
{
    public array $errors = [];


    public function __construct(array $data = [])
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    public function save(): bool
    {
        $this->validate();

        if (empty($this->errors)) {
            $passwordHash = password_hash($this->password, PASSWORD_DEFAULT);
            $sql = 'INSERT INTO users (name, email, password_hash)
                VALUES (:name, :email, :password_hash)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password_hash', $passwordHash, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    public function validate(): void
    {
        if ($this->name == '') {
            $this->errors[] = 'Name is required';
        }

        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Invalid email';
        }

        if (strlen($this->password) < 6) {
            $this->errors[] = 'Please enter at least 6 characters for the password';
        }

        if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
            $this->errors[] = 'Password needs at least one letter';
        }

        if (preg_match('/.*\d+.*/i', $this->password) == 0) {
            $this->errors[] = 'Password needs at least one number';
        }

        if (static::emailExists($this->email)) {
            $this->errors[] = 'Email already taken';
        }
    }

    public static function emailExists(string $email): bool
    {
        return !is_null(static::findByEmail($email));
    }

    public static function findByEmail(string $email): ?User
    {
        $sql = 'SELECT * FROM users
                WHERE email = :email';

        $stmt = static::getDB()->prepare($sql);
        $stmt->bindValue('email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $user = $stmt->fetch();

        return $user ? $user : null;
    }

    public static function authenticate(string $email, string $password): ?User
    {
        $user = static::findByEmail($email);

        if ($user && password_verify($password, $user->password_hash)) {
            return $user;
        }

        return null;
    }
}
