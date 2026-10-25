<?php

declare(strict_types=1);

namespace App\Dto\Category;

use App\Dto\Dto;
use App\Entity\Category\Category;
use App\Entity\Seo\Seo;

class CategoryDto extends Dto
{
    public function __construct(
        public int $id, // TODO: Use UUID7
        public ?Category $parent = null,
        public ?int $position = null,
        public ?int $level = null,
        public ?string $name = null,
        public ?string $nameUrl = null,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $body = null,
        public bool $hidden = false,
        public ?Seo $seo = null,
    ) {
    }

    public static function fromEntity(Category $entity): self
    {
        return new self(
            id: $entity->getId(),
            parent: $entity->getParent(),
            position: $entity->getPosition(),
            level: $entity->getLevel(),
            name: $entity->getName(),
            nameUrl: $entity->getNameUrl(),
            title: $entity->getTitle(),
            description: $entity->getDescription(),
            body: $entity->getBody(),
            hidden: $entity->isHidden(),
            seo: $entity->getSeo(),
        );
    }
}
