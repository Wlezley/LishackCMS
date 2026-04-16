<?php

declare(strict_types=1);

namespace App\Models\StorageSystem\Entity;

use App\Exception\StorageSystemException;
use App\Models\Helpers\IntegerHelper;
use App\Models\Helpers\StringHelper;
use Nette\Utils\DateTime;

final class StorageTree
{
    public ?int $id = null;
    public int $parentId = 0;
    public int $ownerId = 0;
    public ?int $position = null;
    public string $name = '';
    public string $nameUrl = '';
    public ?DateTime $createdAt = null;
    public ?DateTime $modifiedAt = null;
    public ?DateTime $deletedAt = null;
    // public int ?$modifiedBy = null; // TODO ...
    // public int ?$deletedBy = null; // TODO ...

    /**
     * Create an instance from the database record
     *
     * @param array<string,mixed> $row
     * @return StorageTree
     */
    public static function fromDatabaseRow(array $row): self
    {
        $file = new self();

        $file->id = IntegerHelper::toIntOrNull($row['id']);
        $file->ownerId = (int) $row['owner_id'];
        $file->position = IntegerHelper::toIntOrNull($row['position']);
        $file->name = (string) $row['name'];
        $file->nameUrl = (string) $row['name_url'];
        $file->createdAt = $row['created_at']; // new DateTime($row['created_at']);
        $file->modifiedAt = $row['modified_at']; // new DateTime($row['modified_at']);
        $file->deletedAt = $row['deleted_at']; // new DateTime($row['deleted_at']);
        // $file->modifiedBy = IntegerHelper::toIntOrNull($row['modified_by']);
        // $file->deletedBy = IntegerHelper::toIntOrNull($row['deleted_by']);

        return $file;
    }

    /**
     * Returns prepared data for database INSERT/UPDATE (without PK `id`)
     *
     * @return array<string,mixed>
     */
    public function toDatabaseRow(): array
    {
        return [
            // 'id' => $this->id,
            'owner_id' => $this->ownerId,
            'position' => $this->position,
            'name' => $this->name,
            'name_url' => $this->nameUrl,
            // 'created_at' => $this->createdAt,
            // 'modified_at' => $this->modifiedAt,
            'deleted_at' => $this->deletedAt,
            // 'modified_by' => $this->modifiedBy,
            // 'deleted_by' => $this->deletedBy,
        ];
    }

    public function validate(): void
    {
        try {
            StringHelper::assertEmpty($this->name, 'name');
            StringHelper::assertEmpty($this->nameUrl, 'name_url');
        } catch (\InvalidArgumentException $e) {
            throw new StorageSystemException($e->getMessage());
        }
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
