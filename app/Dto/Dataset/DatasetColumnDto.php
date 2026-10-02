<?php

declare(strict_types=1);

namespace App\Dto\Dataset;

use App\Entity\Dataset\Dataset;
use App\Entity\DatasetColumn\DatasetColumn;
use App\Enum\Dataset\DatasetColumnType;

class DatasetColumnDto
{
    public function __construct(
        public Dataset $dataset,
        public int $columnId,
        public string $name,
        public string $slug,
        public DatasetColumnType $type,
        public bool $required = false,
        public bool $listed = false,
        public bool $hidden = false,
        public bool $deleted = false,
    ) {
    }

    public static function fromEntity(DatasetColumn $datasetColumn): self
    {
        return new self(
            dataset: $datasetColumn->getDataset(),
            columnId: $datasetColumn->getColumnId(),
            name: $datasetColumn->getName(),
            slug: $datasetColumn->getSlug(),
            type: $datasetColumn->getType(),
            required: $datasetColumn->isRequired(),
            listed: $datasetColumn->isListed(),
            hidden: $datasetColumn->isHidden(),
            deleted: $datasetColumn->isDeleted(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'dataset' => $this->dataset, // TODO: return dataset->toArray() ???
            'columnId' => $this->columnId,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type->value, // Beware of potential changes in DatasetColumnType
            'required' => $this->required,
            'listed' => $this->listed,
            'hidden' => $this->hidden,
            'deleted' => $this->deleted,
        ];
    }
}
