<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Models;

use PDO;
use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\S3Helper;
use Ilyamur\PhpMvc\App\Models\Hashtag;

class Post extends \Ilyamur\PhpMvc\Core\Model
{
    const COVER_NAME = 'coverImage';
    const MIME_TYPES = ['image/gif', 'image/png', 'image/jpeg'];

    public array $errors = [];
    public array $hashtags = [];

    public function __construct(array $data = [], array $imgsData = [])
    {
        foreach ($data as $key => $val) {
            $this->$key = htmlspecialchars($val);
        }

        foreach ($imgsData as $key => $val) {
            $this->file[$key] = $val;
        }

        $this->parseHashtagsFromBody();
    }

    private function parseHashtagsFromBody()
    {
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

    private function validateInputImage(): void
    {
        switch ($this->file[static::COVER_NAME]['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->errors[] = 'No file uploaded';
                return;
            case UPLOAD_ERR_INI_SIZE:
                $this->errors[] = 'File is too large';
                return;
            default:
                $this->errors[] = 'File not uploaded';
                return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $this->file[static::COVER_NAME]['tmp_name']);

        if (!in_array($mimeType, static::MIME_TYPES)) {
            $this->errors[] = 'Invalid format';
            return;
        }

        if ($this->file[static::COVER_NAME]['size'] > 650000) {
            $this->errors[] = 'File is too large';
            return;
        }

        $pathinfo = pathinfo($this->file[static::COVER_NAME]['name']);
        $base = $pathinfo['filename'];

        $base = mb_substr(preg_replace('/[^a-zA-Z0-0_-]/', '_', $base), 0, 200);

        $destination = __DIR__ . "/../../../uploads/$base." . $pathinfo['extension'];

        $i = 1;
        while (file_exists($destination)) {
            $filename = $base . "-$i" . '.' . $pathinfo['extension'];
            $destination = "../uploads/$filename";
            $i++;
        }

        if (!move_uploaded_file($this->file[static::COVER_NAME]['tmp_name'], $destination)) {
            $this->errors[] = 'Something wrong with file upload';
            return;
        }

        $this->file['destination'] = $destination;
    }

    public function save(): bool
    {
        $this->validate();

        if (file_exists($this->file[static::COVER_NAME]['tmp_name'])) {
            $this->validateInputImage();
        }

        if (empty($this->errors)) {
            $sql = 'INSERT INTO posts (title, body, user_id, cover_link)
                    VALUES (:title, :body, :user_id, :cover_link)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $imageUrl = isset($this->file['destination']) ? $this->saveToS3() :  null;

            $stmt->bindValue(':title', $this->title, PDO::PARAM_STR);
            $stmt->bindValue(':body', $this->body, PDO::PARAM_STR);
            $stmt->bindValue(':user_id', Auth::getUser()->id, PDO::PARAM_INT);
            $stmt->bindValue(':cover_link', $imageUrl, PDO::PARAM_STR);

            return $stmt->execute() && Hashtag::save($this, (int) $db->lastInsertId());
        }

        return false;
    }

    private function saveToS3(): string
    {
        $s3 = new S3Helper();
        return $s3->uploadFile($this->file['destination']);
    }

    public static function getPosts(): array
    {
        $db = static::getDB();

        $result = $db->query(
            'SELECT title, body,
                p.id AS id,
                u.id AS authorId,
                p.cover_link AS url,
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

    public function update(array $data, array $imgsData): bool
    {
        $this->title = $data['title'];
        $this->body = $data['body'];

        foreach ($imgsData as $key => $val) {
            $this->file[$key] = $val;
        }

        $this->validate();

        if (file_exists($this->file[static::COVER_NAME]['tmp_name'])) {
            $this->validateInputImage();
        }

        if (empty($this->errors)) {
            $s3url = isset($this->file['destination']) ? $this->saveToS3() :  null;

            $str = $s3url ? ', cover_link = :cover_link' : '';

            $sql = "UPDATE posts
                    SET title = :title,
                        body = :body" . $str .
                " WHERE id = :id";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue('title', $this->title, PDO::PARAM_STR);
            $stmt->bindValue('body', $this->body, PDO::PARAM_STR);
            $stmt->bindValue('id', $this->id, PDO::PARAM_INT);
            if ($s3url) {
                $stmt->bindValue('cover_link', $s3url, PDO::PARAM_STR);
            }

            $isCorrect = $stmt->execute();

            if (isset($this->cover_link) && $s3url && $isCorrect) {
                $s3 = new S3Helper();
                $s3->deleteFile($this->cover_link);
            }

            return $isCorrect;
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

        $isCorrect = $stmt->execute();

        if (isset($this->cover_link) && $isCorrect) {
            $s3 = new S3Helper();
            $s3->deleteFile($this->cover_link);
        }

        return $isCorrect;
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
