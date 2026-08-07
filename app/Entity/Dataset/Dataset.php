<?php

declare(strict_types=1);

namespace App\Entity\Dataset;

use App\Entity\BaseEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dataset')]
class Dataset extends BaseEntity
{
    #[ORM\Column(type: Types::TEXT, length: 50)]
    private string $name;

    #[ORM\Column(type: Types::TEXT, length: 50)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT, length: 50)]
    private string $component;

    #[ORM\Column(type: Types::TEXT, length: 50)]
    private string $presenter;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $active = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $deleted = false;

    public function __construct(
        string $name,
        string $slug,
        string $component,
        string $presenter,
        bool $active = true,
        bool $deleted = false,
    ) {
        $this->name = $name;
        $this->slug = $slug;
        $this->component = $component;
        $this->presenter = $presenter;
        $this->active = $active;
        $this->deleted = $deleted;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    public function setComponent(string $component): void
    {
        $this->component = $component;
    }

    public function getPresenter(): string
    {
        return $this->presenter;
    }

    public function setPresenter(string $presenter): void
    {
        $this->presenter = $presenter;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }
}
