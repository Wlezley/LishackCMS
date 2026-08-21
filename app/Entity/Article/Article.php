<?php

declare(strict_types=1);

namespace App\Entity\Article;

use App\Entity\BaseEntity;
use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\UpdatedAtTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'article')]
class Article extends BaseEntity
{
    use CreatedAtTrait;
    use UpdatedAtTrait;

//    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
//    private ?string $metaKeywords = null;
//    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
//    private ?Language $language = null;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $nameUrl = null,
        #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => 1])]
        private ?int $categoryId = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $title = null,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $content = null,
        #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
        private ?bool $published = null,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
        private ?\DateTimeImmutable $publishedAt = null,
        #[ORM\Column(type: Types::INTEGER, nullable: true)]
        private ?int $userId = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $robots = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $canonicalUrl = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $ogTitle = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $ogDescription = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $ogImage = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $ogUrl = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $ogType = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $metaTitle = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $metaDescription = null,
    ) {
    }

    public function getNameUrl(): string
    {
        return $this->nameUrl;
    }

    public function setNameUrl(string $nameUrl): void
    {
        $this->nameUrl = $nameUrl;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getRobots(): string
    {
        return $this->robots;
    }

    public function setRobots(string $robots): void
    {
        $this->robots = $robots;
    }

    public function getCanonicalUrl(): string
    {
        return $this->canonicalUrl;
    }

    public function setCanonicalUrl(string $canonicalUrl): void
    {
        $this->canonicalUrl = $canonicalUrl;
    }

    public function getOgTitle(): string
    {
        return $this->ogTitle;
    }

    public function setOgTitle(string $ogTitle): void
    {
        $this->ogTitle = $ogTitle;
    }

    public function getOgDescription(): string
    {
        return $this->ogDescription;
    }

    public function setOgDescription(string $ogDescription): void
    {
        $this->ogDescription = $ogDescription;
    }

    public function getOgImage(): string
    {
        return $this->ogImage;
    }

    public function setOgImage(string $ogImage): void
    {
        $this->ogImage = $ogImage;
    }

    public function getOgUrl(): string
    {
        return $this->ogUrl;
    }

    public function setOgUrl(string $ogUrl): void
    {
        $this->ogUrl = $ogUrl;
    }

    public function getOgType(): string
    {
        return $this->ogType;
    }

    public function setOgType(string $ogType): void
    {
        $this->ogType = $ogType;
    }

    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }
}
