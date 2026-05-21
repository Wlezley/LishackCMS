<?php

declare(strict_types=1);

namespace App\Models\StorageSystem\Repository;

use App\Exception\StorageSystemException;
use App\Models\StorageSystem\Entity\StorageFile;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

class StorageFilesRepository
{
    public const TABLE_NAME = 'storage_files';

    public function __construct(
        private Explorer $db
    ) {
    }

    public function findById(int $id): ?StorageFile
    {
        $row = $this->db->table(self::TABLE_NAME)
            ->where('id', $id)
            ->fetch();

        return $row ? StorageFile::fromDatabaseRow($row->toArray()) : null;
    }

    /** @return StorageFile[] */
    public function getFilesInFolder(int $treeId = 0): array
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->where('tree_id', $treeId)
            ->order('position')
            ->fetchAll();

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

        /** @var ActiveRow $row */
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

    public function setDeleted(int $id, bool $isDeleted): int
    {
        return $this->db->table(self::TABLE_NAME)
            ->where('id', $id)
            ->update([
                'deleted_at' => $isDeleted
                    ? $this->db::literal('NOW()')
                    : null,
            ]);
    }

    public function moveToFolder(int $id, int $treeId): int
    {
        return $this->db->table(self::TABLE_NAME)
            ->where(['id' => $id])
            ->update(['tree_id' => $treeId]);
    }
}
