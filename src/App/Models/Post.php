<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Models;

use PDO;

class Post extends \Ilyamur\PhpMvc\Core\Model
{
    public static function getPosts(): array
    {
        $db = static::getDB();

        $result = $db->query(
            'SELECT
                title,
                body,
                p.id AS id,
                u.id AS authorId,
                p.created_at AS createdAt,
                u.created_at AS authorRegDate,
                u.name AS author
            FROM posts AS p
            JOIN users AS u
            ON u.id = p.user_id
            ORDER BY p.created_at DESC'
        );

        return $result->fetchAll(PDO::FETCH_CLASS, get_called_class());
    }
}
