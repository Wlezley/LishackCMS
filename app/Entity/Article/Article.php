<?php

declare(strict_types=1);

namespace App\Entity\Article;

use App\Entity\BaseEntity;
use App\Entity\Category\Category;
use App\Entity\Seo\Seo;
use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\HasIdTrait;
use App\Entity\Trait\UpdatedAtTrait;
use App\Entity\User\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'article')]
class Article extends BaseEntity
{
    use HasIdTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;

//    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
//    private ?string $metaKeywords = null;
//    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
//    private ?Language $language = null;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 255)]
        private string $title,
        #[ORM\Column(type: Types::STRING, length: 255)]
        private string $nameUrl,
        #[ORM\ManyToOne(targetEntity: Category::class)]
        #[ORM\JoinColumn(
            name: 'category_id',
            referencedColumnName: 'id',
            nullable: true,
            onDelete: 'SET NULL',
        )]
        private ?Category $category = null,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $content = null,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
        private ?\DateTimeImmutable $publishedAt = null,
        #[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(
            name: 'user_id',
            referencedColumnName: 'id',
            nullable: true,
            onDelete: 'SET NULL',
        )]
        private ?User $user = null,
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getNameUrl(): string
    {
        return $this->nameUrl;
    }

    public function setNameUrl(string $nameUrl): void
    {
        $this->nameUrl = $nameUrl;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getSeo(): ?Seo
    {
        return $this->seo;
    }

    public function setSeo(?Seo $seo): void
    {
        $this->seo = $seo;
    }
}
