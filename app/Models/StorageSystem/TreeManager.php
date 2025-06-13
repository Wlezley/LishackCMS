<?php

declare(strict_types=1);

namespace App\Models\StorageSystem;

use App\Models\Helpers\StringHelper;
use App\Models\StorageSystem\Entity\StorageFile;
use App\Models\StorageSystem\Entity\StorageTree;
use App\Models\StorageSystem\Repository\StorageFilesRepository;
use App\Models\StorageSystem\Repository\StorageTreeRepository;

class TreeManager
{
    public function __construct(
        private StorageTreeRepository $storageTreeRepository,
        private StorageFilesRepository $storageFileRepository
    ) {}

    /**
     * Retrieves a virtual folder by its ID.
     *
     * @param int $id The ID of the folder to retrieve.
     * @return StorageTree|null The folder record, or null if not found.
     */
    public function getFolderById(int $id): ?StorageTree
    {
        return $this->storageTreeRepository->findById($id);
    }

    /**
     * Retrieves all folders in a specific virtual folder.
     *
     * @param int $parent_id The ID of the parent folder to filter by, or '0' for root folders (default).
     * @return StorageTree[] An array of virtual folder records associated with the folder.
     */
    public function getChildFolders(int $parent_id = 0): array
    {
        return $this->storageTreeRepository->getFoldersInFolder($parent_id);
    }

    /**
     * Retrieves all files in a specific virtual folder.
     *
     * @param int $tree_id The ID of the folder to retrieve files from, or '0' for files in the root folder (default).
     * @return StorageFile[] An array of file records associated with the folder.
     */
    public function getAllFiles(int $tree_id = 0): array
    {
        return $this->storageFileRepository->getFilesInFolder($tree_id);
    }

    /**
     * Creates a new virtual folder in the database.
     *
     * @param string $name The name of the folder.
     * @param int $parent_id The ID of the parent folder, or '0' for root folders.
     * @return int|null The primary key of the newly created folder, or null if fails.
     */
    public function createFolder(string $name, int $parent_id = 0): ?int
    {
        $tree = new StorageTree();
        $tree->name = $name;
        $tree->name_url = StringHelper::webalize($name);
        $tree->parent_id = $parent_id;

        $tree = $this->storageTreeRepository->insert($tree);

        return $tree->getId();
    }

    /**
     * Deletes a virtual folder by its ID.
     *
     * @param int $id The ID of the folder to delete.
     * @return void
     *
     * @todo Delete all file associations in this folder
     */
    public function deleteFolder(int $id): void
    {
        // $this->storageTreeRepository->delete($id); // Hard delete
        $this->storageTreeRepository->setDeleted($id); // Soft delete
    }

    /**
     * Updates a virtual folder's name.
     *
     * @param int $id The ID of the folder to update.
     * @param string $name The new name for the folder.
     * @param string $name_url Optional name_url for the folder.
     * @return void
     */
    public function renameFolder(int $id, string $name, ?string $name_url = null): void
    {
        if ($name_url === null) {
            $name_url = StringHelper::webalize($name);
        }

        $tree = $this->storageTreeRepository->findById($id);

        if ($tree) {
            $tree->name = $name;
            $tree->name_url = $name_url;

            $this->storageTreeRepository->update($tree);
        }
    }

    /**
     * Move a virtual folder to another virtual folder.
     *
     * @param int $tree_id The ID of the folder to move.
     * @param int $parent_id The ID of the target virtual folder.
     * @param bool $recursive Option to move data recursive (with its content).
     * @return void
     *
     * @todo Add option to move folder content recursively
     */
    public function moveToFolder(int $tree_id, int $parent_id, bool $recursive = true): void
    {
        if ($recursive) {
            // ...
        }

        $this->storageTreeRepository->moveToFolder($tree_id, $parent_id);
    }
}
