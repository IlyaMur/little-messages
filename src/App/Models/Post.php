<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Models;

class Post extends \Ilyamur\PhpMvc\Core\Model
{
    public static function getPosts()
    {
        $db = static::getDB();
        $result = $db->query('SELECT * FROM posts');

        return $result->fetchAll();
    }
}
