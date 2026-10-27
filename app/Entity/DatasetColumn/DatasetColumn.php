<?php

declare(strict_types=1);

namespace App\Entity\DatasetColumn;

use App\Entity\BaseEntity;
use App\Entity\Dataset\Dataset;
use App\Entity\Trait\HasIdTrait;
use App\Enum\Dataset\DatasetColumnType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(
    name: 'dataset_column',
)]
#[ORM\UniqueConstraint(
    name: 'dataset_id_slug_unique',
    columns: ['dataset_id', 'slug'],
)]
#[ORM\UniqueConstraint(
    name: 'dataset_id_column_id_unique',
    columns: ['dataset_id', 'column_id'],
)]
class DatasetColumn extends BaseEntity
{
    use HasIdTrait;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Dataset::class)]
        #[ORM\JoinColumn(
            name: 'dataset_id',
            referencedColumnName: 'id',
            nullable: false,
            onDelete: 'CASCADE',
        )]
        private Dataset $dataset,
        #[ORM\Column(type: Types::INTEGER)]
        private int $columnId,
        #[ORM\Column(type: Types::STRING, length: 50)]
        private string $name,
        #[ORM\Column(type: Types::STRING, length: 50)]
        private string $slug,
        #[ORM\Column(type: Types::STRING, length: 50, enumType: DatasetColumnType::class)]
        private DatasetColumnType $type,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
        private bool $required = false,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
        private bool $listed = false,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
        private bool $hidden = false,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
        private bool $deleted = false,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $defaultValue = null,
    ) {
    }

    public function getDataset(): Dataset
    {
        return $this->dataset;
    }

    public function setDataset(Dataset $dataset): self
    {
        $this->dataset = $dataset;
        return $this;
    }

    public function getColumnId(): int
    {
        return $this->columnId;
    }

    /**
     * Retrieves the unique identifier for a column name by prepending 'data_' to the column ID.
     *
     * @return string The formatted column name identifier.
     */
    public function getColumnNameId(): string
    {
        return 'data_' . $this->columnId;
    }

    public function setColumnId(int $columnId): self
    {
        $this->columnId = $columnId;
        return $this;
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
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getType(): DatasetColumnType
    {
        return $this->type;
    }

    public function setType(DatasetColumnType $type): self
    {
        $this->type = $type;

        // TODO: Check if type is valid and his default value is set to ??? null ???

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;
        return $this;
    }

    public function isListed(): bool
    {
        return $this->listed;
    }

    public function setListed(bool $listed): self
    {
        $this->listed = $listed;
        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;
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

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(?string $defaultValue): self
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }
}
