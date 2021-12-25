<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App;

use Aws\S3\S3Client;

class S3Helper
{
    const BUCKET_FOLDER = 'my_posts';
    const ACL_PUBLIC_READ = 'public-read';

    private S3Client $client;

    public function __construct()
    {
        $this->client = new S3Client(
            [
                'version' => 'latest',
                'region' => getenv('S3_REGION'),
                'credentials' => [
                    'key' => getenv('S3_ACCESS_KEY'),
                    'secret' => getenv('S3_SECRET_KEY'),
                ],
            ]
        );
    }

    private static function generateFileDestinationName(string $fileSrc): string
    {
        return sprintf(
            '%s/%s/%s',
            static::BUCKET_FOLDER,
            substr(md5($fileSrc), 0, 2),
            basename($fileSrc)
        );
    }

    public function uploadFile(string $fileSrc)
    {
        $source = fopen($fileSrc, 'rb');

        $result = $this->client->upload(
            getenv('S3_BUCKET_NAME'),
            static::generateFileDestinationName($fileSrc),
            $source,
            static::ACL_PUBLIC_READ
        );

        if ($result['@metadata']['statusCode'] !== 200) {
            throw new \Exception("File upload fail");
        }

        if (empty($result['ObjectURL'])) {
            throw new \Exception("No ObjectURL found");
        }

        return $result['ObjectURL'];
    }

    public function deleteFile(string $objectUrl): void
    {
        $filekey = str_replace(getenv('S3_URL'), '', $objectUrl);

        $this->client->deleteObject(
            [
                'Bucket' => getenv('S3_BUCKET_NAME'),
                'Key'    => $filekey
            ]
        );
    }
}
