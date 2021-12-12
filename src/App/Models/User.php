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

    public function save(): false
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

    public function validate()
    {
        // Name
        if ($this->name == '') {
            $this->errors[] = 'Name is required';
        }

        // email address
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Invalid email';
        }

        // Password
        if ($this->password != $this->password_confirmation) {
            $this->errors[] = 'Password must match confirmation';
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
    }
}
