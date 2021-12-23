<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Models;

use PDO;
use Ilyamur\PhpMvc\App\Auth;

class Post extends \Ilyamur\PhpMvc\Core\Model
{
    public array $errors = [];
    public array $hashtags = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $val) {
            $this->$key = htmlspecialchars($val);
        }

        preg_match_all(Hashtag::HASHTAG_REGEXP, $this->body, $this->hashtags);
    }

    protected function validate(): void
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

            return $stmt->execute() && Hashtag::save($this, (int) $db->lastInsertId());
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

        $posts = $result->fetchAll(PDO::FETCH_CLASS, get_called_class());

        foreach ($posts as $post) {
            $post->insertLinksToHashtags();
        }

        return $posts;
    }

    public static function findById(int $postsId): ?Post
    {
        $sql = 'SELECT 
                    u.name AS author,
                    p.*
                FROM posts AS p
                JOIN users AS u
                ON p.user_id = u.id
                WHERE p.id = :id';

        $db = static::getDB();

        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $postsId, PDO::PARAM_INT);

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $post = $stmt->fetch();

        if ($post) {
            $post->insertLinksToHashtags();
            return $post;
        }

        return null;
    }

    public function update(array $data): bool
    {
        $this->title = $data['title'];
        $this->body = $data['body'];

        $this->validate();

        if (empty($this->errors)) {

            $sql = 'UPDATE posts
                    SET title = :title,
                        body = :body
                    WHERE id = :id';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue('title', $this->title, PDO::PARAM_STR);
            $stmt->bindValue('body', $this->body, PDO::PARAM_STR);
            $stmt->bindValue('id', $this->id, PDO::PARAM_INT);

            return $stmt->execute();
        }

        return false;
    }

    public function delete(): bool
    {
        $sql = 'DELETE FROM posts
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue('id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function findPostsByHashtag(string $hashtag): array
    {
        $sql = 'SELECT DISTINCT title, body,
                    p.id AS id,
                    p.user_id AS authorId,
                    p.created_at AS createdAt,
                    (SELECT users.name FROM users WHERE users.id = p.user_id) AS author
                FROM posts AS p
                JOIN hashtags_posts AS hp
                ON p.id = hp.post_id
                JOIN hashtags AS h
                ON hp.hashtag_id = h.id
                JOIN users AS u
                ON u.id = p.user_id
                WHERE h.hashtag = :hashtag';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue('hashtag', strtolower($hashtag), PDO::PARAM_STR);

        $stmt->execute();

        $posts = $stmt->fetchAll(PDO::FETCH_CLASS, get_called_class());

        foreach ($posts as $post) {
            $post->insertLinksToHashtags();
        }

        return $posts;
    }

    protected function insertLinksToHashtags(): void
    {
        $this->body = preg_replace(Hashtag::HASHTAG_REGEXP, ' <a href="/hashtags/show/$1"> #$1</a> ', $this->body);
    }
}
