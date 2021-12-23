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

        $insertTagsSql = 'INSERT INTO hashtags (hashtag)
                          VALUES (:hashtag)';
        $stmt = $db->prepare($insertTagsSql);

        foreach (array_unique($post->hashtags[0]) as $hashtag) {
            $stmt->bindValue('hashtag', strtolower(substr($hashtag, 1)), PDO::PARAM_STR);

            if (
                !$stmt->execute() ||
                !$db->query("INSERT INTO hashtags_posts VALUES ({$db->lastInsertId()}, $postId)")
            ) {
                return false;
            };
        }

        return true;
    }

    static function getLastHashtags($number = 10)
    {
        $sql = "SELECT hashtag AS body, id
                FROM hashtags 
                ORDER BY id DESC
                LIMIT $number";

        return static::getDB()->query($sql)->fetchAll();
    }
}
