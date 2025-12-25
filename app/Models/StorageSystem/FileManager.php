<?php

declare(strict_types=1);

namespace App\Models\StorageSystem;

use App\Exception\StorageSystemException;
use App\Models\Helpers\StringHelper;
use App\Models\StorageSystem\Entity\StorageFile;
use App\Models\StorageSystem\Repository\StorageFilesRepository;

class FileManager
{
    public function __construct(
        private StorageFilesRepository $storageFilesRepository
    ) {
    }

    /**
     * Retrieves file info by its ID.
     *
     * @param int $id The ID of the file to retrieve.
     * @return StorageFile|null The file record, or null if not found.
     */
    public function getFileById(int $id): ?StorageFile
    {
        return $this->storageFilesRepository->findById($id);
    }

    /**
     * Creates new file info in the database.
     *
     * @param string $name The name of the file.
     * @param int $treeId The ID of the parent file, or '0' for root files.
     * @return int|null The primary key of the newly created file, or null if fails.
     *
     * @todo Add more parameters according to upload data
     */
    public function createFile(string $name, int $treeId = 0): ?int
    {
        $file = new StorageFile();
        $file->name = $name;
        $file->nameUrl = StringHelper::webalize($name);
        $file->treeId = $treeId;

        $file = $this->storageFilesRepository->insert($file);

        return $file->getId();
    }

    /**
     * Deletes file info by its ID.
     *
     * @param int $id The ID of the file to delete.
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
     * @param string|null $nameUrl Optional name_url for the file.
     * @throws StorageSystemException
     */
    public function renameFile(int $id, string $name, ?string $nameUrl = null): void
    {
        if ($nameUrl === null) {
            $nameUrl = StringHelper::webalize($name);
        }

        $file = $this->storageFilesRepository->findById($id);

        if ($file) {
            $file->name = $name;
            $file->nameUrl = $nameUrl;

            $this->storageFilesRepository->update($file);
        }
    }

    /**
     * Move a file to a virtual folder.
     *
     * @param int $id The ID of the file to move.
     * @param int $treeId The ID of the target virtual folder.
     * @return int Affected rows
     */
    public function moveToFolder(int $id, int $treeId): int
    {
        return $this->storageFilesRepository->moveToFolder($id, $treeId);
    }
}
