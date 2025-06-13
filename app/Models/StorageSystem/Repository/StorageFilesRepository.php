<?php

declare(strict_types=1);

namespace App\Models\StorageSystem\Repository;

use App\Models\StorageSystem\Entity\StorageFile;
use App\Models\StorageSystem\StorageSystemException;
use Nette\Database\Explorer;

class StorageFilesRepository
{
    public const TABLE_NAME = 'storage_files';

    public function __construct(
        private Explorer $db
    ) {}

    public function findById(int $id): ?StorageFile
    {
        $row = $this->db->table(self::TABLE_NAME)
            ->where('id', $id)
            ->fetch();

        return $row ? StorageFile::fromDatabaseRow($row->toArray()) : null;
    }

    /** @return StorageFile[] */
    public function getFilesInFolder(int $tree_id = 0): array
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->where('tree_id', $tree_id);
            // ->order('position')
            // ->fetchAll();

        $result = [];
        foreach ($query as $row) {
            $result[] = StorageFile::fromDatabaseRow($row->toArray());
        }

        return $result;
    }

    public function insert(StorageFile $file): StorageFile
    {
        $file->setId(null);
        $file->validate();

        $row = $this->db->table(self::TABLE_NAME)->insert($file->toDatabaseRow());
        $file->id = (int) $row->getPrimary();
        return $file;
    }

    public function update(StorageFile $file): void
    {
        $file->validate();

        if ($file->id === null) {
            throw new StorageSystemException('Cannot update file without ID.');
        }

        $this->db->table(self::TABLE_NAME)
            ->where('id', $file->id)
            ->update($file->toDatabaseRow());
    }

    public function delete(int $id): int
    {
        return $this->db->table(self::TABLE_NAME)
            ->where('id', $id)
            ->delete();
    }

    public function setDeleted(int $id): int
    {
        return $this->db->table(self::TABLE_NAME)
            ->where('id', $id)
            ->update([$this->db::literal('deleted_at = NOW()')]); // TODO: Check if this works
    }

    public function setUndelete(int $id): int
    {
        return $this->db->table(self::TABLE_NAME)
            ->where('id', $id)
            ->update([$this->db::literal('deleted_at = NULL')]); // TODO: Check if this works
    }

    public function moveToFolder(int $id, int $tree_id): int
    {
        return $this->db->table(self::TABLE_NAME)
            ->where(['id' => $id])
            ->update(['tree_id' => $tree_id]);
    }
}
