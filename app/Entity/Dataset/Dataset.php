<?php

declare(strict_types=1);

namespace App\Entity\Dataset;

use App\Entity\BaseEntity;
use App\Entity\Trait\HasIdTrait;
use App\Models\Helpers\StringHelper;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Entity]
#[ORM\Table(name: 'dataset')]
class Dataset extends BaseEntity
{
    use HasIdTrait;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 50)]
        private string $name,
        #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
        private ?string $slug = null,
        #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['default' => null])]
        private ?string $component = null,
        #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['default' => null])]
        private ?string $presenter = null,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
        private bool $active = true,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
        private bool $deleted = false,
    ) {
        if ($slug === null) {
            $this->slug = StringHelper::slugize($this->name);
        }
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

    public function getSlug(): string
    {
        Assert::notNull($this->slug, 'Slug is not set');
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        if ($slug === null) {
            $slug = StringHelper::slugize($this->name);
        }
        $this->slug = $slug;
        return $this;
    }

    public function getComponent(): ?string
    {
        return $this->component;
    }

    public function setComponent(?string $component): self
    {
        $this->component = $component;
        return $this;
    }

    public function getPresenter(): ?string
    {
        return $this->presenter;
    }

    public function setPresenter(?string $presenter): self
    {
        $this->presenter = $presenter;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return array<string, string|bool|null>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'component' => $this->component,
            'presenter' => $this->presenter,
            'active' => $this->active,
            'deleted' => $this->deleted,
        ];
    }
}
