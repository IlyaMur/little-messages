<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Service;

use Aws\S3\S3Client;

/**
 * S3Helper
 *
 * PHP version 8.0
 */
class S3Helper
{
    /**
     * Folder name for image storing
     *
     * @var string
     */
    private const BUCKET_FOLDER = 'my_posts';

    /**
     * Access type for S3
     *
     * @var string
     */
    private const ACL_PUBLIC_READ = 'public-read';

    private S3Client $client;

    /**
     * Class constructor. Set parameters to S3Client object
     *
     * @return void
     */
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

    /**
     * Generate destination for uploading file
     * 
     * @param string $fileSrc File source
     * @param string $type File type (avatar or post cover)
     * 
     * @return string Path to the file
     */
    private static function generateFileDestinationName(string $fileSrc, string $type): string
    {
        return sprintf(
            '%s/%s/%s/%s',
            static::BUCKET_FOLDER,
            $type,
            bin2hex(random_bytes(5)),
            basename($fileSrc)
        );
    }

    /**
     * Upload file to S3
     * 
     * @param string $fileSrc File source
     * @param string $type File type (avatar or post cover)
     * 
     * @return string URL
     */
    public function uploadFile(string $fileSrc, string $type): string
    {
        $source = fopen($fileSrc, 'rb');

        $result = $this->client->upload(
            getenv('S3_BUCKET_NAME'),
            static::generateFileDestinationName($fileSrc, $type),
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

    /**
     * Deleting file from S3
     * 
     * @param string $objectUrl File URL
     * 
     * @return void
     */
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
