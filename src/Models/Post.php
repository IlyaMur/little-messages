<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Models;

use PDO;
use Ilyamur\PhpMvc\Service\Auth;
use Ilyamur\PhpMvc\Models\Hashtag;

/**
 * Post model
 *
 * PHP version 8.0
 */
class Post extends BaseModel
{
    /**
     * Image type for uploading
     *
     * @var string
     */
    protected const IMAGE_TYPE = 'coverImage';

    /**
     * Error messages
     *
     * @var array
     */
    public array $errors = [];

    /**
     * Posts hashtags
     *
     * @var array
     */
    public array $hashtags = [];

    /**
     * Class constructor
     *
     * @param array $data Initial property values (optional)
     * @param array $imgsData uploaded image data (optional)
     * 
     * @return void
     */
    public function __construct(array $data = [], array $imgsData = [])
    {
        foreach ($data as $key => $val) {
            $this->$key = htmlspecialchars($val);
        }

        foreach ($imgsData as $key => $val) {
            $this->file[$key] = $val;
        }

        // parse hashtags from post body
        $this->parseHashtagsFromBody();
    }

    /**
     * Finding hashtags in the post body by regexp
     *
     * @return void
     */
    private function parseHashtagsFromBody(): void
    {
        preg_match_all(Hashtag::HASHTAG_REGEXP, $this->body, $this->hashtags);
    }

    /**
     * Validate post
     *
     * @return void
     */
    protected function validate(): void
    {
        if (trim($this->title) === '') {
            $this->errors[] = 'Title is required';
        }

        if (trim($this->body) === '') {
            $this->errors[] = 'Posts body is required';
        }
    }

    /**
     * Validate posts image
     *
     * @return void
     */
    private function validateInputImage(): void
    {
        switch ($this->file[static::IMAGE_TYPE]['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->errors[] = 'No file uploaded';
                break;
            case UPLOAD_ERR_INI_SIZE:
                $this->errors[] = 'File is too large';
                break;
            default:
                $this->errors[] = 'File not uploaded';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $this->file[static::IMAGE_TYPE]['tmp_name']);

        // validation of uploaded file by allowed image types
        if (!in_array($mimeType, static::MIME_TYPES)) {
            $this->errors[] = 'Invalid format';
        }

        // checking image size
        if ($this->file[static::IMAGE_TYPE]['size'] > 250000) {
            $this->errors[] = 'File is too large';
        }
    }

    /**
     * Save posts to db
     *
     * @return bool
     */
    public function save(): bool
    {
        $this->validate();
        $isFileUploaded = file_exists($this->file[static::IMAGE_TYPE]['tmp_name']);

        if ($isFileUploaded) {
            $this->validateInputImage();
        }

        if (empty($this->errors)) {
            if ($isFileUploaded) {
                $this->generateUploadDestination();

                $imgUrl = AWS_STORING ? $this->saveToS3(type: 'coverImage') : $this->file['destination'];
            } else {
                $imgUrl = null;
            }

            $sql = 'INSERT INTO posts (title, body, user_id, cover_link)
                    VALUES (:title, :body, :user_id, :cover_link)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':title', $this->title, PDO::PARAM_STR);
            $stmt->bindValue(':body', $this->body, PDO::PARAM_STR);
            $stmt->bindValue(':user_id', Auth::getUser()->id, PDO::PARAM_INT);
            $stmt->bindValue(':cover_link', $imgUrl, PDO::PARAM_STR);

            return $stmt->execute() && Hashtag::save($this, (int) $db->lastInsertId());
        }

        return false;
    }

    /**
     * Get posts from db
     * 
     * @param array $page Page number (optional)
     * @param array $limit Pagination limit per page (optional)
     * 
     * @return array
     */
    public static function getPosts(int $page = 1, int $limit = 3): array
    {
        $db = static::getDB();
        $offset = $limit * ($page - 1);

        $result = $db->query(
            "SELECT title, body,
                p.id AS id, 
                u.id AS authorId,
                p.cover_link AS url,
                p.created_at AS createdAt,
                u.created_at AS authorRegDate,
                u.name AS author
            FROM posts AS p
            JOIN users AS u
            ON u.id = p.user_id
            ORDER BY createdAt DESC
            LIMIT $limit
            OFFSET $offset"
        );

        $posts = $result->fetchAll(PDO::FETCH_CLASS, static::class);

        foreach ($posts as $post) {
            $post->insertLinksToHashtags();
        }

        return $posts;
    }

    /**
     * Get specific post from db by id
     * 
     * @param int $postsId Post id
     *
     * @return array
     */
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
        $stmt->setFetchMode(PDO::FETCH_CLASS, static::class);

        $post = $stmt->fetch();

        if ($post) {
            $post->insertLinksToHashtags();
            return $post;
        }

        return null;
    }

    /**
     * Get total posts count
     * 
     * @param int $postsId Post id
     *
     * @return mixed
     */
    public static function getTotalCount(): string|false
    {
        $result = static::getDB()->query('SELECT count(id) AS total FROM posts');

        return $result->fetchColumn();
    }

    public function update(array $data, array $imgsData): bool
    {
        // handle original hashtags
        $this->parseHashtagsFromBody();
        $originalHashtags = $this->hashtags;

        $this->title = htmlspecialchars($data['title']);
        $this->body = htmlspecialchars($data['body']);

        // handle new hashtags
        $this->parseHashtagsFromBody();
        // select deleted tags
        $deletedTags = array_filter($originalHashtags[0], fn ($tag) => !in_array($tag, $this->hashtags[0]));

        foreach ($imgsData as $key => $val) {
            $this->file[$key] = $val;
        }

        $this->validate();

        $isFileUploaded = file_exists($this->file[static::IMAGE_TYPE]['tmp_name']);

        if ($isFileUploaded) {
            $this->validateInputImage();
        }

        if (empty($this->errors)) {
            if ($isFileUploaded) {
                $this->generateUploadDestination();
                $imgUrl = AWS_STORING ? $this->saveToS3(type: static::IMAGE_TYPE) : $this->file['destination'];
            }

            $sql = "UPDATE posts
                    SET title = :title, body = :body";

            if (isset($imgUrl)) {
                $sql .= ', cover_link = :cover_link';
            }

            $sql .= " WHERE id = :id";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue('title', $this->title, PDO::PARAM_STR);
            $stmt->bindValue('body', $this->body, PDO::PARAM_STR);
            $stmt->bindValue('id', $this->id, PDO::PARAM_INT);
            if (isset($imgUrl)) {
                $stmt->bindValue('cover_link', $imgUrl, PDO::PARAM_STR);
            }

            $isCorrect = $stmt->execute();

            if (
                isset($this->cover_link) &&
                isset($imgUrl) &&
                $isCorrect
            ) {
                static::deleteFromStorage($this->cover_link, static::IMAGE_TYPE);
            }

            return ($isCorrect && Hashtag::save(post: $this, postId: (int) $this->id, deletedTags: $deletedTags));
        }

        return false;
    }

    /**
     * Delete specific post
     * 
     * @return bool
     */
    public function delete(): bool
    {
        $sql = 'DELETE FROM posts
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue('id', $this->id, PDO::PARAM_INT);

        $isCorrect = $stmt->execute();

        if (isset($this->cover_link) && $isCorrect) {
            static::deleteFromStorage($this->cover_link, static::IMAGE_TYPE);
        }

        return $isCorrect;
    }

    /**
     * Find specific post by its hashtag
     * 
     * @return array
     */
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

        $posts = $stmt->fetchAll(PDO::FETCH_CLASS, static::class);

        foreach ($posts as $post) {
            $post->insertLinksToHashtags();
        }

        return $posts;
    }

    /**
     * Insert <a> tags to posts body
     * 
     * @return void
     */
    protected function insertLinksToHashtags(): void
    {
        $this->body = preg_replace(Hashtag::HASHTAG_REGEXP, '<a href="/hashtags/show/$1">#$1</a>', $this->body);
    }
}
