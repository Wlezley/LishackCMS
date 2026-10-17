<?php

declare(strict_types=1);

namespace App\Entity\Category;

use App\Entity\BaseEntity;
use App\Entity\Seo\Seo;
use App\Entity\Trait\HasIdTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'category')]
class Category extends BaseEntity
{
    use HasIdTrait;

//        #[ORM\ManyToOne(targetEntity: Language::class)]
//        #[ORM\JoinColumn(
//            name: 'language_code',
//            referencedColumnName: 'code',
//            nullable: true,
//            onDelete: 'SET NULL',
//        )]
//        private ?Language $language = null,

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Category::class)]
        #[ORM\JoinColumn(
            name: 'parent_id',
            referencedColumnName: 'id',
            nullable: true,
            onDelete: 'SET NULL',
        )]
        private ?Category $parent = null,
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
        #[ORM\ManyToOne(targetEntity: Seo::class)]
        #[ORM\JoinColumn(
            name: 'seo_id',
            referencedColumnName: 'id',
            nullable: true,
            onDelete: 'SET NULL',
        )]
        private ?Seo $seo = null,
    ) {
    }

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function setParent(?Category $parent): self
    {
        $this->parent = $parent;
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

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getNameUrl(): ?string
    {
        return $this->nameUrl;
    }

    public function setNameUrl(?string $nameUrl): self
    {
        $this->nameUrl = $nameUrl;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;
        return $this;
    }

    public function getSeo(): ?Seo
    {
        return $this->seo;
    }

    public function setSeo(?Seo $seo): self
    {
        $this->seo = $seo;
        return $this;
    }
}
