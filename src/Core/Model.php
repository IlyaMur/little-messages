<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Core;

use PDO;
use Ilyamur\PhpMvc\Config\Config;

abstract class Model
{
    protected static function getDB()
    {
        static $db = null;

        if ($db === null) {
            $host = Config::DB_HOST;
            $dbname = Config::DB_NAME;
            $username = Config::DB_USER;
            $password = Config::DB_PASSWORD;

            $db = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $username,
                $password
            );

            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $db;
    }
}
