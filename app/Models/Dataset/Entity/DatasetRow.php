<?php

declare(strict_types=1);

namespace App\Models\Dataset\Entity;

use App\Models\Dataset\DatasetException;
use Nette\Utils\Json;

final class DatasetRow
{
    public ?int $id = null;

    /** @var array<int,mixed> $values */
    public array $values = [];

    /**
     * Create an instance from the database record
     *
     * @param array<string,mixed> $row
     * @param DatasetColumn[] $columns
     * @return DatasetRow
     */
    public static function fromDatabaseRow(array $row, array $columns): self
    {
        $instance = new self();
        $instance->id = isset($row['id']) ? (int) $row['id'] : null;

        /** @var DatasetColumn $column */
        foreach ($columns as $column) {
            $key = \App\Models\Dataset\Repository\DataRepository::DATA_COLUMN_PREFIX . $column->columnId;

            if (!array_key_exists($key, $row)) {
                continue;
            }

            $value = $column->formatValueByType($row[$key]);
            $instance->values[$column->slug] = $value;
            $instance->values[$column->columnId] = $value;
        }

        return $instance;
    }

    /**
     * Returns prepared data for database INSERT/UPDATE
     *
     * @return array<string,mixed>
     */
    public function toDatabaseRow(): array
    {
        $data = [];
        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        // TODO: Use column ID, or column slug? Which one has higher priority?
        foreach ($this->values as $columnId => $value) {
            $key = \App\Models\Dataset\Repository\DataRepository::DATA_COLUMN_PREFIX . $columnId;

            if (is_array($value)) {
                $data[$key] = Json::encode($value);
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /** @param DatasetColumn[] $columns */
    public function validate(array $columns): void
    {
        /** @var DatasetColumn $column */
        foreach ($columns as $column) {
            $columnId = $column->columnId;
            $value = $this->values[$columnId] ?? null;

            if ($column->required && $value === null) {
                throw new DatasetException("Dataset column '{$column->slug}' is required.");
            }

            if ($value !== null && !$column->isValidValue($value)) {
                throw new DatasetException("Invalid value for '{$column->slug}'.");
            }
        }
    }



    /** @return array<int,mixed> */
    public function getValues(): array
    {
        return $this->values;
    }

    public function getValue(int $columnId): mixed
    {
        return $this->values[$columnId] ?? null;
    }

    public function setValue(int $columnId, mixed $value): void
    {
        $this->values[$columnId] = $value;
    }
}
