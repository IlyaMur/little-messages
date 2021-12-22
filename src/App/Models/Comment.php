<?php

namespace Ilyamur\PhpMvc\App\Models;

use PDO;
use Ilyamur\PhpMvc\App\Auth;

class Comment extends \Ilyamur\PhpMvc\Core\Model
{
    public array $errors = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    public function validate(): void
    {
        if (trim($this->body) === '') {
            $this->errors[] = 'Comments body is required';
        }
    }

    public static function getCommentsById(int $postsId): array
    {
        $sql = 'SELECT 
                    u.name AS author, 
                    c.body AS body,
                    c.id AS id,
                    u.id AS userId,
                    c.created_at as createdAt
                FROM comments AS c
                JOIN users AS u
                ON c.user_id = u.id
                WHERE c.post_id = :post_id';

        $db = static::getDB();

        $stmt = $db->prepare($sql);
        $stmt->bindValue('post_id', $postsId, PDO::PARAM_INT);

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        return $stmt->fetchAll();
    }

    public function save(): bool
    {
        $this->validate();

        if (empty($this->errors)) {
            $sql = 'INSERT INTO posts (title, body, user_id)
                    VALUES (:title, :body, :user_id)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':title', $this->title, PDO::PARAM_STR);
            $stmt->bindValue(':body', $this->body, PDO::PARAM_STR);
            $stmt->bindValue(':user_id', Auth::getUser()->id, PDO::PARAM_INT);

            return $stmt->execute();
        }

        return false;
    }
}
