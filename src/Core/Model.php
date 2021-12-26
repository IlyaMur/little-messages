<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Core;

use PDO;
use Exception;
use Ilyamur\PhpMvc\App\S3Helper;
use Ilyamur\PhpMvc\Config\Config;

abstract class Model
{
    protected static function getDB()
    {
        static $db = null;

        if ($db === null) {
            $host = getenv('DB_HOST');
            $dbname = getenv('DB_NAME');
            $username = getenv('DB_USER');
            $password = getenv('DB_PASSWORD');

            $db = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $username,
                $password
            );

            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $db;
    }

    protected function saveToS3(string $type): string
    {
        $s3 = new S3Helper();
        return $s3->uploadFile($this->file['destination'], $type);
    }

    protected function generateUploadDestination(string $type = 'coverImage'): void
    {
        $pathinfo = pathinfo($this->file[$type]['name']);

        $base = $pathinfo['filename'];

        $base = mb_substr(preg_replace('/[^a-zA-Z0-0_-]/', '_', $base), 0, 200);

        if (Config::AWS_STORING) {
            $destination = dirname($this->file[$type]["tmp_name"]) . '/' .  $base . '.' . $pathinfo['extension'];

            rename($this->file[$type]['tmp_name'], $destination);
        } else {
            $filename = "$base." . $pathinfo['extension'];
            $uploadPath = __DIR__ . "/../../public/uploads/$type/$filename";

            $i = 1;
            while (file_exists($uploadPath)) {
                $filename = $base . "-$i." . $pathinfo['extension'];
                $uploadPath = __DIR__ . "/../../public/uploads/$type/$filename";
                $i++;
            }

            if (!move_uploaded_file($this->file[$type]['tmp_name'], $uploadPath)) {
                throw new Exception('Error caused file uploading');
            }

            $destination = "/uploads/$type/$filename";
        }

        $this->file['destination'] = $destination;
    }
}
