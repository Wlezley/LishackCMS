<?php

declare(strict_types=1);

namespace App\Dto\Article;

use App\Dto\Dto;
use App\Entity\Article\Article;
use App\Entity\Category\Category;
use App\Entity\Seo\Seo;
use App\Entity\User\User;

class ArticleDto extends Dto
{
    public function __construct(
        public int $id, // TODO: Use UUID7
        public \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
        public string $title,
        public string $nameUrl,
        public ?Category $category = null,
        public ?string $content = null,
        public ?\DateTimeImmutable $publishedAt = null,
        public ?User $user = null,
        public ?Seo $seo = null,
    ) {
    }

    public static function fromEntity(Article $entity): self
    {
        return new self(
            id: $entity->getId(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
            title: $entity->getTitle(),
            nameUrl: $entity->getNameUrl(),
            category: $entity->getCategory(),
            content: $entity->getContent(),
            publishedAt: $entity->getPublishedAt(),
            user: $entity->getUser(),
            seo: $entity->getSeo(),
        );
    }
}
