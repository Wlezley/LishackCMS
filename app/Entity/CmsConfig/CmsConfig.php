<?php

declare(strict_types=1);

namespace App\Entity\CmsConfig;

use App\Enum\CmsConfigCategoryEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cms_config')]
class CmsConfig
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
        private string $key,
        #[ORM\Column(type: Types::STRING, length: CmsConfigCategoryEnum::MAX_LENGTH, enumType: CmsConfigCategoryEnum::class)]
        private CmsConfigCategoryEnum $category,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $value = null,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getCategory(): CmsConfigCategoryEnum
    {
        return $this->category;
    }

    public function setCategory(CmsConfigCategoryEnum $category): void
    {
        $this->category = $category;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): void
    {
        $this->value = $value;
    }
}
