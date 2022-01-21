<?php

namespace Ilyamur\PhpMvc\Models;

use PDO;
use Ilyamur\PhpMvc\Service\Auth;

/**
 * Comment model
 *
 * PHP version 8.0
 */
class Comment extends BaseModel
{
    /**
     * Error messages
     *
     * @var array
     */
    public array $errors = [];

    /**
     * Class constructor
     *
     * @param array $data Initial property values (optional)
     * 
     * @return void
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     * Validate comment and check its captcha
     * 
     * @param array $data Initial property values (optional)
     *
     * @return void
     */
    public function validate(string $correctCaptcha): void
    {
        if (trim($this->commentBody) === '') {
            $this->errors[] = 'Write something in a comment...';
        }

        // if user anonym check captcha
        if (!Auth::getUser()) {
            if ($this->captcha !== $correctCaptcha) {
                $this->errors[] = 'Incorrect captcha';

                $this->captchaError = true;
            }
        }
    }

    /**
     * Get comments by posts id
     * 
     * @param id $postsId specific post id
     *
     * @return array
     */
    public static function getCommentsByPostId(int $postsId): array
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
        $stmt->setFetchMode(PDO::FETCH_CLASS, static::class);

        return $stmt->fetchAll();
    }

    /**
     * Save comment to db
     * 
     * @param string $captcha captcha code
     *
     * @return bool
     */
    public function save(string $captcha): bool
    {
        // validate captcha
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

    /**
     * Get number of last comments from DB
     * 
     * @param int $number Number of comments
     *
     * @return array
     */
    public static function getLastComments(int $number = 5): array
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

        $result = static::getDB()->query($sql, PDO::FETCH_CLASS, static::class);

        return $result->fetchAll();
    }

    /**
     * Get comments by specific user id
     * 
     * @param int $userId User id
     * @param int $limit Pagination limit (optional)
     * @param int $page number of page (optional)
     *
     * @return array
     */
    public static function getCommentsByUserId(int $userId, int $limit = 5, int $page = 1): array
    {
        $offset = $limit * ($page - 1);

        $sql = "SELECT
                    u.id AS authorId,
                    u.name AS author,
                    u.ava_link AS authorAvatar,
                    p.title AS postTitle,
                    p.id AS postId,
                    c.post_id AS postId,
                    c.body AS body,
                    c.created_at AS createdAt
                FROM comments AS c
                JOIN users AS u
                ON c.user_id = u.id
                JOIN posts AS p
                ON c.post_id = p.id
                WHERE u.id = $userId
                ORDER BY c.created_at DESC
                LIMIT $limit
                OFFSET $offset";

        return static::getDB()->query($sql, PDO::FETCH_CLASS, static::class)->fetchAll();
    }

    /**
     * Get total comments count by specific user id
     * 
     * @param int $userId User id
     *
     * @return mixed
     */
    public static function getTotalCountByUserId(int $userId): ?int
    {
        $db = static::getDB();
        $stmt = $db->prepare("SELECT count(id) AS total FROM comments WHERE user_id = :user_id");

        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn() ?: null;
    }
}
