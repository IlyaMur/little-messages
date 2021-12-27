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

    public function validate(string $correctCaptcha): void
    {
        if (trim($this->commentBody) === '') {
            $this->errors[] = 'Write something in a comment...';
        }

        if (!Auth::getUser()) {
            if ($this->captcha !== $correctCaptcha) {
                $this->errors[] = 'Incorrect captcha';

                $this->captchaError = true;
            }
        }
    }

    public static function getCommentsById(int $postsId): array
    {
        $sql = 'SELECT 
                    u.name AS author,
                    u.ava_link AS authorAvatar,
                    c.body AS body,
                    c.id AS id,
                    u.id AS userId,
                    c.created_at as createdAt
                FROM comments AS c
                LEFT JOIN users AS u
                ON c.user_id = u.id
                WHERE c.post_id = :post_id
                ORDER BY c.created_at DESC';

        $db = static::getDB();

        $stmt = $db->prepare($sql);
        $stmt->bindValue('post_id', $postsId, PDO::PARAM_INT);

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        return $stmt->fetchAll();
    }

    public function save(string $captcha): bool
    {
        $this->validate($captcha);

        if (empty($this->errors)) {
            $sql = 'INSERT INTO comments (body, user_id, post_id)
                    VALUES (:body, :user_id, :post_id)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $userId = Auth::getUser() ? Auth::getUser()->id : null;

            $stmt->bindValue(':body', $this->commentBody, PDO::PARAM_STR);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':post_id', $this->postId, PDO::PARAM_INT);

            return $stmt->execute();
        }

        return false;
    }

    static function getLastComments(int $number = 5): array
    {
        $sql = "SELECT
                    c.created_at AS createdAt,
                    u.id AS authorId,
                    u.name AS author,
                    u.ava_link AS authorAvatar,
                    c.body AS body,
                    p.title AS postTitle,
                    p.id AS postId
                FROM comments AS c
                LEFT JOIN users AS u
                ON c.user_id = u.id
                JOIN posts AS p
                ON c.post_id = p.id
                ORDER BY c.created_at DESC
                LIMIT $number";

        $result = static::getDB()->query($sql, PDO::FETCH_CLASS, get_called_class());

        return $result->fetchAll();
    }
}
