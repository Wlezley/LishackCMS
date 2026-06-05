<?php

declare(strict_types=1);

namespace App\Entity\CmsConfig;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cms_config')]
class CmsConfig
{
    #[ORM\Id]
    #[ORM\Column(type: Types::TEXT, length: 255, unique: true)]
    private string $key;

    #[ORM\Column(type: Types::TEXT, length: 255)]
    private string $category;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $value;

    public function __construct(
        string $key,
        string $category,
        ?string $value = null,
    ) {
        $this->key = $key;
        $this->category = $category;
        $this->value = $value;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
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
