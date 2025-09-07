<?php

namespace App\Service;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class CloudinaryUploader
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        // Initialise Cloudinary à partir de la variable d'environnement
        $this->cloudinary = new Cloudinary($_ENV['CLOUDINARY_URL']);
    }

    /**
     * Upload un fichier et retourne les infos
     */
    public function uploadFile(string $filePath)
    {
        return $this->cloudinary->uploadApi()->upload($filePath, ['folder' => 'BlogSphere', 'curl' => [CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 0]]);
    }

    /**
     * Retourne l'URL d'une image déjà uploadée
     */
    public function getUrl(string $publicId, array $options = []): string
    {
        return $this->cloudinary->image($publicId)->toUrl($options);
    }
}
