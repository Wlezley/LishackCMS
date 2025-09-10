<?php

declare(strict_types=1);

namespace App\Models\StorageSystem\Entity;

use App\Models\Helpers\IntegerHelper;
use App\Models\Helpers\StringHelper;
use App\Models\StorageSystem\StorageSystemException;
use Nette\Utils\DateTime;

final class StorageFile
{
    public ?int $id = null;
    public int $tree_id = 0;
    public int $owner_id = 0;
    public ?int $position = null;
    public string $name = '';
    public string $name_url = '';
    public string $type = '';
    public string $icon = 'unknown';
    public int $size = 0;
    public string $checksum = '';
    public string $storage_id = '';
    public string $download_id = '';
    public ?DateTime $uploaded_at = null;
    public ?DateTime $modified_at = null;
    public ?DateTime $deleted_at = null;
    // public int ?$modified_by = null; // TODO ...
    // public int ?$deleted_by = null; // TODO ...

    /**
     * Create an instance from the database record
     *
     * @param array<string,mixed> $row
     * @return StorageFile
     */
    public static function fromDatabaseRow(array $row): self
    {
        $file = new self();

        $file->id = IntegerHelper::toIntOrNull($row['id']);
        $file->tree_id = (int) $row['tree_id'];
        $file->owner_id = (int) $row['owner_id'];
        $file->position = IntegerHelper::toIntOrNull($row['position']);
        $file->name = (string) $row['name'];
        $file->name_url = (string) $row['name_url'];
        $file->type = (string) $row['type'];
        $file->icon = (string) $row['icon'];
        $file->size = (int) $row['size'];
        $file->checksum = (string) $row['checksum'];
        $file->storage_id = (string) $row['storage_id'];
        $file->download_id = (string) $row['download_id'];
        $file->uploaded_at = $row['uploaded_at']; // new DateTime($row['uploaded_at']);
        $file->modified_at = $row['modified_at']; // new DateTime($row['modified_at']);
        $file->deleted_at = $row['deleted_at']; // new DateTime($row['deleted_at']);
        // $file->modified_by = IntegerHelper::toIntOrNull($row['modified_by']);
        // $file->deleted_by = IntegerHelper::toIntOrNull($row['deleted_by']);

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
            'tree_id' => $this->tree_id,
            'owner_id' => $this->owner_id,
            'position' => $this->position,
            'name' => $this->name,
            'name_url' => $this->name_url,
            'type' => $this->type,
            'icon' => $this->icon,
            'size' => $this->size,
            'checksum' => $this->checksum,
            'storage_id' => $this->storage_id,
            'download_id' => $this->download_id,
            // 'uploaded_at' => $this->uploaded_at,
            // 'modified_at' => $this->modified_at,
            'deleted_at' => $this->deleted_at,
            // 'modified_by' => $this->modified_by,
            // 'deleted_by' => $this->deleted_by,
        ];
    }

    /**
     * @throws StorageSystemException
     */
    public function validate(): void
    {
        try {
            StringHelper::assertEmpty($this->name, 'name');
            StringHelper::assertEmpty($this->name_url, 'name_url');
            StringHelper::assertEmpty($this->type, 'type');
            StringHelper::assertEmpty($this->checksum, 'checksum');
            StringHelper::assertEmpty($this->storage_id, 'storage_id');
            StringHelper::assertEmpty($this->download_id, 'download_id');
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
