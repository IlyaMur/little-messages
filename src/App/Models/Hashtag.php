<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Models;

use PDO;
use Ilyamur\PhpMvc\App\Models\Post;

class Hashtag extends \Ilyamur\PhpMvc\Core\Model
{
    const HASHTAG_REGEXP = '/\#([а-яa-z]+)/isu';

    public static function save(Post $post, int $postId): bool
    {
        $db = static::getDB();

        $insertTagsSql = 'INSERT INTO hashtags (hashtag)
                          VALUES (:hashtag)';
        $stmt = $db->prepare($insertTagsSql);

        foreach ($post->hashtags[0] as $hashtag) {
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
}
