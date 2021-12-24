<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Models;

use PDO;
use Ilyamur\PhpMvc\App\Models\Post;

class Hashtag extends \Ilyamur\PhpMvc\Core\Model
{
    const HASHTAG_REGEXP = '/\#([а-яa-z]+)/iu';

    public static function save(Post $post, int $postId): bool
    {
        $db = static::getDB();

        $insertTagsSql = 'INSERT INTO hashtags (hashtag) VALUES (:hashtag)';

        foreach (array_unique($post->hashtags[0]) as $hashtag) {
            $tag = static::getDuplicateTag($hashtag);

            if ($tag) {
                $isCorrect = $db->query("INSERT INTO hashtags_posts VALUES ({$tag['id']}, $postId)");
            } else {
                $stmt = $db->prepare($insertTagsSql);
                $stmt->bindValue('hashtag', strtolower(substr($hashtag, 1)), PDO::PARAM_STR);

                $isCorrect = $stmt->execute() && $db->query("INSERT INTO hashtags_posts VALUES ({$db->lastInsertId()}, $postId)");
            }

            if (!$isCorrect) {
                return false;
            }
        }

        return true;
    }

    static function getDuplicateTag(string $hashtag): ?string
    {
        $db = static::getDB();

        $stmt = $db->prepare('SELECT * FROM hashtags WHERE hashtag = :hashtag');

        $stmt->bindValue('hashtag', strtolower(substr($hashtag, 1)), PDO::PARAM_STR);
        $stmt->execute();

        $tag = $stmt->fetch();

        return $tag ? $tag : null;
    }

    static function getLastActualHashtags($number = 10)
    {
        $sql = "SELECT hashtag AS body, id
                FROM hashtags_posts as hp
                JOIN hashtags AS h
                ON h.id = hp.hashtag_id
                ORDER BY id DESC
                LIMIT $number";

        return static::getDB()->query($sql)->fetchAll();
    }
}
