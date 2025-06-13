<?php

declare(strict_types=1);

namespace App\Models\StorageSystem;

use App\Models\StorageSystem\Repository\StorageFilesRepository;

class FileUploader
{
    // public const UPLOAD_DIR = 'uploads/files/';
    // public const UPLOAD_URL = HOME_URL . 'uploads/files/';

    public function __construct(
        // private Storage $storage,
        // private StorageFilesRepository $repository,
    ) {}

    // /**
    //  * Save uploaded file and create DB entry.
    //  *
    //  * @param FileUpload $file Uploaded file data object.
    //  * @return int inserted row ID
    //  */
    // public function upload(FileUpload $file): int
    // {
    //     if (!$file->isOk() || !$file->isImage()) {
    //         throw new \InvalidArgumentException('Invalid file upload');
    //     }

    //     $ext = strtolower(pathinfo($file->getSanitizedName(), PATHINFO_EXTENSION));
    //     $basename = Strings::webalize(pathinfo($file->getSanitizedName(), PATHINFO_FILENAME));
    //     $filename = $basename . '-' . uniqid() . '.' . $ext;

    //     // Save original file
    //     $filepath = PROJECT_DIR . self::UPLOAD_DIR . $filename;
    //     $file->move($filepath);

    //     // Save metadata to DB
    //     $row = $this->db->table(self::TABLE_NAME)->insert([
    //         'filename' => $filename,
    //         'path'     => self::UPLOAD_DIR,
    //         'title'    => $title,
    //         'alt'      => $alt,
    //         'mime'     => $file->getContentType(),
    //         'size'     => $file->getSize(),
    //     ]);

    //     return (int) $row['id'];
    // }

    // public function upload(FileUpload $upload, ?int $userId = null): int
    // {
    //     if (!$upload->isOk()) {
    //         throw new \RuntimeException('File upload failed.');
    //     }

    //     $path = $this->storage->storeFile($upload);

    //     return $this->repository->insertFile([
    //         'storage_path' => $path,
    //         'original_name' => $upload->getUntrustedName(),
    //         'mime_type' => $upload->getContentType(),
    //         'size' => $upload->getSize(),
    //         'created_at' => new DateTime(),
    //         'uploaded_by_user_id' => $userId,
    //     ]);
    // }
}
