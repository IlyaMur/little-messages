<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Models;

use PDO;
use Exception;
use Ilyamur\PhpMvc\Service\S3Helper;

/**
 * Base model
 *
 * PHP version 8.0
 */
abstract class BaseModel
{
    /**
     * Array of allowed image types
     *
     * @var array
     */
    public const MIME_TYPES = ['image/gif', 'image/png', 'image/jpeg'];

    /**
     * Get the PDO database connection
     *
     * @return mixed
     */
    protected static function getDB()
    {
        static $db = null;

        if ($db === null) {
            $host = DB_HOST;
            $dbname = DB_NAME;
            $username = DB_USER;
            $password = DB_PASSWORD;

            $db = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $username,
                $password
            );

            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $db;
    }

    /**
     * Upload file to S3 storage
     *
     * @return mixed
     */
    protected function saveToS3(string $type): string
    {
        $s3 = new S3Helper();
        return $s3->uploadFile($this->file['destination'], $type);
    }

    /**
     * Generate the destination to upload.
     * Validates a file and selects destination between local and S3 storages
     *
     * @return void
     */
    protected function generateUploadDestination(string $type = 'coverImage'): void
    {
        $pathinfo = pathinfo($this->file[$type]['name']);
        $base = $pathinfo['filename'];
        $base = mb_substr(preg_replace('/[^a-zA-Z0-0_-]/', '_', $base), 0, 200);
        if (AWS_STORING) {
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

    /**
     * Check if link external or not
     *
     * @return bool
     */
    public static function isLinkExternal(string $link): bool
    {
        $res = preg_match('/amazon/', $link);

        return !!$res;
    }

    /**
     * Deletinig file from storage
     *
     * @return void
     */
    public static function deleteFromStorage(string $link, string $type): void
    {
        if (static::isLinkExternal($link)) {
            $s3 = new S3Helper();
            $s3->deleteFile($link);
        } else {
            unlink(__DIR__ . "/../../public/uploads/$type/" . basename($link));
        }
    }
}
