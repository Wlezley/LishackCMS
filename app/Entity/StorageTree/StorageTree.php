<?php

declare(strict_types=1);

namespace App\Entity\StorageTree;

use App\Entity\BaseEntity;
use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\HasIdTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'storage_tree')]
class StorageTree extends BaseEntity
{
    use HasIdTrait;

    use CreatedAtTrait;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $parentId = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $ownerId = 0;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => null])]
    private ?int $position = null;

    public function __construct(
        int $parentId,
        int $ownerId,
        ?int $position,
        #[ORM\Column(type: Types::STRING, length: 255)]
        private string $name,
        #[ORM\Column(type: Types::STRING, length: 255)]
        private string $nameUrl,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
        private ?\DateTimeImmutable $modifiedAt = null,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
        private ?\DateTimeImmutable $deletedAt = null,
    ) {
        $this->parentId = $parentId;
        $this->ownerId = $ownerId;
        $this->position = $position;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function setParentId(int $parentId): self
    {
        $this->parentId = $parentId;
        return $this;
    }

    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    public function setOwnerId(int $ownerId): self
    {
        $this->ownerId = $ownerId;
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getNameUrl(): string
    {
        return $this->nameUrl;
    }

    public function setNameUrl(string $nameUrl): self
    {
        $this->nameUrl = $nameUrl;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getModifiedAt(): ?\DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(?\DateTimeImmutable $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;
        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deletedAt = $deleted ? new \DateTimeImmutable() : null;
        return $this;
    }

    public function isModified(): bool
    {
        return $this->modifiedAt !== null;
    }

    public function setModified(bool $modified): self
    {
        $this->modifiedAt = $modified ? new \DateTimeImmutable() : null;
        return $this;
    }
}
