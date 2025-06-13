<?php

declare(strict_types=1);

namespace App\Models\StorageSystem;

use Nette\Http\FileUpload;

class Storage
{
    public function __construct(
    ) {}

    public function storeFile(FileUpload $file): string
    {
        $ext = pathinfo($file->getSanitizedName(), PATHINFO_EXTENSION);
        $hash = sha1(uniqid((string) random_int(0, 99999), true));

        $subDir = substr($hash, 0, 2) . '/' . substr($hash, 2, 2);
        $targetDir = PROJECT_DIR . 'upload/files/' . $subDir;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $targetPath = $targetDir . '/' . $hash . '.' . $ext;
        $file->move($targetPath);

        return $subDir . '/' . basename($targetPath);
    }
}
