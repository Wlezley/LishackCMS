<?php

declare(strict_types=1);

namespace App\Models\StorageSystem\Repository;

use App\Models\StorageSystem\Entity\StorageTree;
use App\Models\StorageSystem\StorageSystemException;
use Nette\Database\Explorer;

class StorageTreeRepository
{
    public const TABLE_NAME = 'storage_tree';

    public function __construct(
        private Explorer $db
    ) {}

    public function findById(int $id): ?StorageTree
    {
        $row = $this->db->table(self::TABLE_NAME)
            ->where('id', $id)
            ->fetch();

        return $row ? StorageTree::fromDatabaseRow($row->toArray()) : null;
    }

    /** @return StorageTree[] */
    public function getFoldersInFolder(int $parent_id = 0): array
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->where('parent_id', $parent_id);
            // ->order('position')
            // ->fetchAll();

        $result = [];
        foreach ($query as $row) {
            $result[] = StorageTree::fromDatabaseRow($row->toArray());
        }

        return $result;
    }

    public function insert(StorageTree $tree): StorageTree
    {
        $tree->setId(null);
        $tree->validate();

        $row = $this->db->table(self::TABLE_NAME)->insert($tree->toDatabaseRow());
        $tree->id = (int) $row->getPrimary();
        return $tree;
    }

    public function update(StorageTree $tree): void
    {
        $tree->validate();

        if ($tree->id === null) {
            throw new StorageSystemException('Cannot update tree without ID.');
        }

        $this->db->table(self::TABLE_NAME)
            ->where('id', $tree->id)
            ->update($tree->toDatabaseRow());
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

    public function moveToFolder(int $id, int $parent_id): int
    {
        return $this->db->table(self::TABLE_NAME)
            ->where(['id' => $id])
            ->update(['parent_id' => $parent_id]);
    }
}
