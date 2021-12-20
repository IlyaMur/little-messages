<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Models;

use PDO;
use Ilyamur\PhpMvc\App\Auth;

class Post extends \Ilyamur\PhpMvc\Core\Model
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
        if (trim($this->title) === '') {
            $this->errors[] = 'Title is required';
        }

        if (trim($this->body) === '') {
            $this->errors[] = 'Posts body is required';
        }
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

    public static function getPosts(): array
    {
        $db = static::getDB();

        $result = $db->query(
            'SELECT title, body,
                p.id AS id,
                u.id AS authorId,
                p.created_at AS createdAt,
                u.created_at AS authorRegDate,
                u.name AS author
            FROM posts AS p
            JOIN users AS u
            ON u.id = p.user_id
            ORDER BY createdAt DESC'
        );

        return $result->fetchAll(PDO::FETCH_CLASS, get_called_class());
    }

    public static function findById(int $postsId): ?Post
    {
        $sql = 'SELECT * from posts
                WHERE id = :id';

        $db = static::getDB();

        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $postsId, PDO::PARAM_INT);

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $post = $stmt->fetch();

        return $post ? $post : null;
    }
}
