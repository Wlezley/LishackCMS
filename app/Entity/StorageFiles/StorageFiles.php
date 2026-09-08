<?php

declare(strict_types=1);

namespace App\Entity\StorageFiles;

use App\Entity\BaseEntity;
use App\Entity\Trait\HasIdTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Entity]
#[ORM\Table(name: 'storage_files')]
class StorageFiles extends BaseEntity
{
    use HasIdTrait;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $treeId = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $ownerId = 0;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => null])]
    private ?int $position = null;

    public function __construct(
        int $treeId,
        int $ownerId,
        ?int $position,
        #[ORM\Column(type: Types::STRING, length: 255)]
        private string $name,
        #[ORM\Column(type: Types::STRING, length: 255)]
        private string $nameUrl,
        #[ORM\Column(type: Types::STRING, length: 255)]
        private string $type,
        #[ORM\Column(type: Types::STRING, length: 5)]
        private string $icon,
        #[ORM\Column(type: Types::BIGINT, options: ['default' => 0])]
        private int $size,
        #[ORM\Column(type: Types::STRING, length: 32)] // TODO: Its MD5 ???
        private string $checksum,
        #[ORM\Column(type: Types::STRING, length: 16)]
        private string $storageId,
        #[ORM\Column(type: Types::STRING, length: 16)]
        private string $downloadId,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
        private \DateTimeImmutable $uploadedAt,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
        private ?\DateTimeImmutable $modifiedAt = null,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
        private ?\DateTimeImmutable $deletedAt = null,
    ) {
        $this->treeId = $treeId;
        $this->ownerId = $ownerId;
        $this->position = $position;
    }

    public function getTreeId(): int
    {
        return $this->treeId;
    }

    public function setTreeId(int $treeId): void
    {
        $this->treeId = $treeId;
    }

    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    public function setOwnerId(int $ownerId): void
    {
        $this->ownerId = $ownerId;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getNameUrl(): string
    {
        return $this->nameUrl;
    }

    public function setNameUrl(string $nameUrl): void
    {
        $this->nameUrl = $nameUrl;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): void
    {
        Assert::maxLength($icon, 5, 'Icon must be max 5 characters long');
        $this->icon = $icon;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    public function getChecksum(): string
    {
        return $this->checksum;
    }

    public function setChecksum(string $checksum): void
    {
        Assert::maxLength($checksum, 32, 'Checksum must be max 32 characters long');
        $this->checksum = $checksum;
    }

    public function getStorageId(): string
    {
        return $this->storageId;
    }

    public function setStorageId(string $storageId): void
    {
        Assert::maxLength($storageId, 16, 'StorageId must be max 16 characters long');
        $this->storageId = $storageId;
    }

    public function getDownloadId(): string
    {
        return $this->downloadId;
    }

    public function setDownloadId(string $downloadId): void
    {
        Assert::maxLength($downloadId, 16, 'DownloadId must be max 16 characters long');
        $this->downloadId = $downloadId;
    }

    public function getUploadedAt(): \DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): void
    {
        $this->uploadedAt = $uploadedAt;
    }

    public function getModifiedAt(): ?\DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(?\DateTimeImmutable $modifiedAt): void
    {
        $this->modifiedAt = $modifiedAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deletedAt = $deleted ? new \DateTimeImmutable() : null;
    }

    public function isModified(): bool
    {
        return $this->modifiedAt !== null;
    }

    public function setModified(bool $modified): void
    {
        $this->modifiedAt = $modified ? new \DateTimeImmutable() : null;
    }
}
