<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Models;

use PDO;
use Ilyamur\PhpMvc\Models\Post;

/**
 * Hashtag model
 *
 * PHP version 8.0
 */
class Hashtag extends BaseModel
{
    /**
     * Hastag regexp for parsing posts body
     *
     * @var string
     */
    public const HASHTAG_REGEXP = '/\#([а-яa-z]+)/iu';

    /**
     * Save hashtag to the db
     *
     * @param Post $post post Model
     * @param int $postId Post id
     * @param array $deletedTags Old tags for deletion
     *
     * @return bool
     */
    public static function save(Post $post, int $postId, array $deletedTags = []): bool
    {
        $db = static::getDB();

        // delete old redundant tags
        foreach ($deletedTags as $deletedTag) {
            Hashtag::deleteTagFromPost($deletedTag, $postId);
        }
        foreach (array_unique($post->hashtags[0]) as $hashtag) {
            // checking if tag already persists
            $tag = static::getDuplicateTag($hashtag);

            if ($tag) {
                // if persists - save to hasht_posts
                $isCorrect = $db->query("INSERT IGNORE INTO hashtags_posts VALUES ($tag->id, $postId)");
            } else {
                // if not persists insert into hashtag table first
                $stmt = $db->prepare('INSERT INTO hashtags (hashtag) VALUES (:hashtag)');
                $stmt->bindValue('hashtag', strtolower(substr($hashtag, 1)), PDO::PARAM_STR);

                $isCorrect = $stmt->execute() &&
                    $db->query("INSERT INTO hashtags_posts VALUES ({$db->lastInsertId()}, $postId)");
            }

            // if something is incorrect return false
            if (!$isCorrect) {
                return false;
            }
        }

        return true;
    }

    /**
     * Find if hashtag already persists in the db
     *
     * @param string $hashtag Hashtag
     *
     * @return mixed
     */
    public static function getDuplicateTag(string $hashtag): Hashtag|false
    {
        $db = static::getDB();

        $stmt = $db->prepare('SELECT * FROM hashtags WHERE hashtag = :hashtag');

        $stmt->bindValue('hashtag', strtolower(substr($hashtag, 1)), PDO::PARAM_STR);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, static::class);

        return $stmt->fetch();
    }

    /**
     * Get number of last hashtags from the db
     *
     * @param int $number Number of hashtags
     *
     * @return array
     */
    public static function getLastActualHashtags(int $number = 10): array
    {
        $sql = "SELECT DISTINCT hashtag AS body, id
                FROM hashtags_posts as hp
                JOIN hashtags AS h
                ON h.id = hp.hashtag_id
                ORDER BY id DESC
                LIMIT $number";

        return static::getDB()->query($sql)->fetchAll();
    }

    /**
     * Delete specific tag from hashtags_posts table
     *
     * @param int $postId Post id
     * @param string $tag Hashtag
     *
     * @return array
     */
    public static function deleteTagFromPost(string $tag, int $postId): bool
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
