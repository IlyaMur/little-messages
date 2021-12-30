<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Models;

use PDO;
use Ilyamur\PhpMvc\App\Models\Post;

class Hashtag extends \Ilyamur\PhpMvc\Core\Model
{
    const HASHTAG_REGEXP = '/\#([а-яa-z]+)/iu';

    public static function save(Post $post, int $postId, $deletedTags = []): bool
    {
        $db = static::getDB();

        foreach ($deletedTags as $deletedTag) {
            Hashtag::deleteTagFromPost($deletedTag, $postId);
        }

        foreach (array_unique($post->hashtags[0]) as $hashtag) {
            $tag = static::getDuplicateTag($hashtag);

            if ($tag) {
                $isCorrect = $db->query("INSERT IGNORE INTO hashtags_posts VALUES ($tag->id, $postId)");
            } else {
                $stmt = $db->prepare('INSERT INTO hashtags (hashtag) VALUES (:hashtag)');
                $stmt->bindValue('hashtag', strtolower(substr($hashtag, 1)), PDO::PARAM_STR);

                $isCorrect = $stmt->execute() && $db->query("INSERT INTO hashtags_posts VALUES ({$db->lastInsertId()}, $postId)");
            }

            if (!$isCorrect) {
                return false;
            }
        }

        return true;
    }

    static function getDuplicateTag(string $hashtag): Hashtag|false
    {
        $db = static::getDB();

        $stmt = $db->prepare('SELECT * FROM hashtags WHERE hashtag = :hashtag');

        $stmt->bindValue('hashtag', strtolower(substr($hashtag, 1)), PDO::PARAM_STR);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, static::class);

        return $stmt->fetch();
    }

    static function getLastActualHashtags($number = 10): array
    {
        $sql = "SELECT DISTINCT hashtag AS body, id
                FROM hashtags_posts as hp
                JOIN hashtags AS h
                ON h.id = hp.hashtag_id
                ORDER BY id DESC
                LIMIT $number";

        return static::getDB()->query($sql)->fetchAll();
    }

    static function deleteTagFromPost(string $tag, int $postId): bool
    {
        $sql = "DELETE FROM hashtags_posts
                WHERE hashtag_id = (SELECT id FROM hashtags WHERE hashtag = :hashtag)
                AND post_id = :post_id;";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue('hashtag', substr($tag, 1), PDO::PARAM_STR);
        $stmt->bindValue('post_id', $postId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
