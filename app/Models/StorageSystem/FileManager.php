<?php

declare(strict_types=1);

namespace App\Models\StorageSystem;

use App\Models\Helpers\StringHelper;
use App\Models\StorageSystem\Entity\StorageFile;
use App\Models\StorageSystem\Repository\StorageFilesRepository;

class FileManager
{
    public function __construct(
        private StorageFilesRepository $storageFilesRepository
    ) {}

    /**
     * Retrieves a file info by its ID.
     *
     * @param int $id The ID of the file to retrieve.
     * @return StorageFile|null The file record, or null if not found.
     */
    public function getFileById(int $id): ?StorageFile
    {
        return $this->storageFilesRepository->findById($id);
    }

    /**
     * Creates a new file info in the database.
     *
     * @param string $name The name of the file.
     * @param int $tree_id The ID of the parent file, or '0' for root files.
     * @return int|null The primary key of the newly created file, or null if fails.
     *
     * @todo Add more parameters according to upload data
     */
    public function createFile(string $name, int $tree_id = 0): ?int
    {
        $file = new StorageFile();
        $file->name = $name;
        $file->name_url = StringHelper::webalize($name);
        $file->tree_id = $tree_id;

        $file = $this->storageFilesRepository->insert($file);

        return $file->getId();
    }

    /**
     * Deletes a file info by its ID.
     *
     * @param int $id The ID of the file to delete.
     * @return void
     *
     * @todo Delete all file associations in this file
     */
    public function deleteFile(int $id): void
    {
        // $this->storageFilesRepository->delete($id); // Hard delete
        $this->storageFilesRepository->setDeleted($id); // Soft delete
    }

    /**
     * Updates a file name.
     *
     * @param int $id The ID of the file to update.
     * @param string $name The new name for the file.
     * @param string $name_url Optional name_url for the file.
     * @return void
     */
    public function renameFile(int $id, string $name, ?string $name_url = null): void
    {
        if ($name_url === null) {
            $name_url = StringHelper::webalize($name);
        }

        $file = $this->storageFilesRepository->findById($id);

        if ($file) {
            $file->name = $name;
            $file->name_url = $name_url;

            $this->storageFilesRepository->update($file);
        }
    }

    /**
     * Move a file to virtual folder.
     *
     * @param int $id The ID of the file to move.
     * @param int $tree_id The ID of the target virtual folder.
     * @return int Affected rows
     */
    public function moveToFolder(int $id, int $tree_id): int
    {
        return $this->storageFilesRepository->moveToFolder($id, $tree_id);
    }
}
