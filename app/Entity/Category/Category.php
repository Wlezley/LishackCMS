<?php

declare(strict_types=1);

namespace App\Entity\Category;

use App\Entity\BaseEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'category')]
class Category extends BaseEntity
{
    public function __construct(
        #[ORM\Column(type: Types::INTEGER, nullable: true)]
        private ?int $parentId = null,
        #[ORM\Column(type: Types::INTEGER, nullable: true)]
        private ?int $position = null,
        #[ORM\Column(type: Types::INTEGER, nullable: true)]
        private ?int $level = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $name = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $nameUrl = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $title = null,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $description = null,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $body = null,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
        private bool $hidden = false,
    ) {
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(?int $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): void
    {
        $this->level = $level;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getNameUrl(): ?string
    {
        return $this->nameUrl;
    }

    public function setNameUrl(?string $nameUrl): void
    {
        $this->nameUrl = $nameUrl;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }
}
