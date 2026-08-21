<?php

declare(strict_types=1);

namespace App\Entity\DatasetColumn;

use App\Entity\BaseEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dataset_column')]
class DatasetColumn extends BaseEntity
{
    public function __construct(
        #[ORM\Column(type: Types::INTEGER, unique: true)] // TODO: Link to column in dataset
        private int $datasetId,
        #[ORM\Column(type: Types::INTEGER, unique: true)] // TODO: Link to column in dataset
        private int $columnId,
        #[ORM\Column(type: Types::STRING, length: 50)]
        private string $name,
        #[ORM\Column(type: Types::STRING, length: 50, unique: true)] // TODO: Must be unique in dataset
        private string $slug,
        #[ORM\Column(type: Types::STRING, length: 50)]
        private string $type,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
        private bool $required = false,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
        private bool $listed = false,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
        private bool $hidden = false,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
        private bool $deleted = false,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $default = null,
    ) {
    }

    public function getDatasetId(): int
    {
        return $this->datasetId;
    }

    public function setDatasetId(int $datasetId): void
    {
        $this->datasetId = $datasetId;
    }

    public function getColumnId(): int
    {
        return $this->columnId;
    }

    public function setColumnId(int $columnId): void
    {
        $this->columnId = $columnId;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    public function isListed(): bool
    {
        return $this->listed;
    }

    public function setListed(bool $listed): void
    {
        $this->listed = $listed;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function setDefault(?string $default): void
    {
        $this->default = $default;
    }
}
