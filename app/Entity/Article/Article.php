<?php

declare(strict_types=1);

namespace App\Entity\Article;

//use App\Entity\Language\Language;

use App\Entity\BaseEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'article')]
class Article extends BaseEntity
{
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private string $nameUrl;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => 1])]
    private int $categoryId;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private string $content;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private bool $published;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $publishedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private int $userId;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private string $robots;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private string $canonicalUrl;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private string $ogTitle;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private string $ogDescription;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private string $ogImage;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private string $ogUrl;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private string $ogType;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private string $metaTitle;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private string $metaDescription;

//    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
//    private string $metaKeywords;

//    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
//    private Language $language;

    public function __construct(
        string $nameUrl,
        int $categoryId,
        string $title,
        string $content,
        bool $published,
        \DateTimeImmutable $publishedAt,
        \DateTimeImmutable $updatedAt,
        int $userId,
        string $robots,
        string $canonicalUrl,
        string $ogTitle,
        string $ogDescription,
        string $ogImage,
        string $ogUrl,
        string $ogType,
        string $metaTitle,
        string $metaDescription,
        // string $metaKeywords,
    ) {
        $this->nameUrl = $nameUrl;
        $this->categoryId = $categoryId;
        $this->title = $title;
        $this->content = $content;
        $this->published = $published;
        $this->publishedAt = $publishedAt;
        $this->updatedAt = $updatedAt;
        $this->userId = $userId;
        $this->robots = $robots;
        $this->canonicalUrl = $canonicalUrl;
        $this->ogTitle = $ogTitle;
        $this->ogDescription = $ogDescription;
        $this->ogImage = $ogImage;
        $this->ogUrl = $ogUrl;
        $this->ogType = $ogType;
        $this->metaTitle = $metaTitle;
        $this->metaDescription = $metaDescription;
//        $this->metaKeywords = $metaKeywords;
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
