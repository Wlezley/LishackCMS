<?php

declare(strict_types=1);

namespace App\Entity\StorageTree;

use App\Entity\BaseEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'storage_tree')]
class StorageTree extends BaseEntity
{
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $parentId = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $ownerId = 0;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $position = null;

    #[ORM\Column(type: Types::TEXT, length: 255)]
    private string $name;

    #[ORM\Column(type: Types::TEXT, length: 255)]
    private string $nameUrl;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $modifiedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct(
        int $parentId,
        int $ownerId,
        ?int $position,
        string $name,
        string $nameUrl,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $modifiedAt = null,
        ?\DateTimeImmutable $deletedAt = null,
    ) {
        $this->parentId = $parentId;
        $this->ownerId = $ownerId;
        $this->position = $position;
        $this->name = $name;
        $this->nameUrl = $nameUrl;
        $this->createdAt = $createdAt;
        $this->modifiedAt = $modifiedAt;
        $this->deletedAt = $deletedAt;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function setParentId(int $parentId): void
    {
        $this->parentId = $parentId;
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
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
