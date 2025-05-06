<?php

declare(strict_types=1);

namespace App\Models\Dataset\Entity;

use App\Models\Dataset\DatasetException;
use App\Models\Helpers\SqlHelper;
use App\Models\Helpers\StringHelper;

final class DatasetColumn
{
    public const ALLOWED_TYPES = [
        'int',
        'bool',
        'string',
        'text',
        'wysiwyg',
        'json',
        'html',
    ];

    public int $datasetId = 0;
    public ?int $columnId = null;
    public string $name = '';
    public string $slug = '';
    public string $type = 'string';
    public bool $required = false;
    public bool $listed = false;
    public bool $hidden = false;
    public bool $deleted = false;
    public ?string $default = null;

    /**
     * Create an instance from the database record
     *
     * @param array<string,mixed> $row
     * @return DatasetColumn
     */
    public static function fromDatabaseRow(array $row): self
    {
        $column = new self();
        $column->datasetId = (int) $row['dataset_id'] ?: 0;
        $column->columnId = isset($row['column_id']) ? (int) $row['column_id'] : null;
        $column->name = (string) ($row['name'] ?? '');
        $column->slug = (string) ($row['slug'] ?? '');
        $column->type = (string) ($row['type'] ?? 'string');
        $column->required = (bool) ($row['required'] ?? false);
        $column->listed = (bool) ($row['listed'] ?? false);
        $column->hidden = (bool) ($row['hidden'] ?? false);
        $column->deleted = (bool) ($row['deleted'] ?? false);
        $column->default = (string) $row['default'];

        return $column;
    }

    /**
     * Returns prepared data for database INSERT/UPDATE (without PK `id`)
     *
     * @return array<string,mixed>
     */
    public function toDatabaseRow(): array
    {
        return [
            'dataset_id' => $this->datasetId,
            'column_id' => $this->columnId,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'required' => $this->required ? 1 : 0,
            'listed' => $this->listed ? 1 : 0,
            'hidden' => $this->hidden ? 1 : 0,
            'deleted' => $this->deleted ? 1 : 0,
            'default' => $this->default,
        ];
    }

    /**
     * Returns the SQL column type
     */
    public function getSqlType(): string
    {
        return match ($this->type) {
            'int' => 'INT(11)',
            'string' => 'VARCHAR(255)',
            'bool' => 'TINYINT(1)',
            'json', 'text', 'html', 'wysiwyg' => 'TEXT',
            default => throw new DatasetException("Unknown SQL type for '{$this->type}'."),
        };
    }

    public function formatValueByType(mixed $value, ?string $type = null): mixed
    {
        $type = $type ?? $this->type;

        return match ($type) {
            'int' => $value ? (int) $value : null,
            'string', 'text', 'html', 'wysiwyg' => (string) $value,
            'bool' => (bool) $value,
            'json' => (string) $value,
            default => $value,
        };
    }

    public function getDatabaseColumnName(): string
    {
        return \App\Models\Dataset\Repository\DataRepository::DATA_COLUMN_PREFIX . $this->columnId;
    }

    /**
     * Returns the SQL column definition for CREATE/ALTER TABLE or ADD/MODIFY COLUMN
     */
    public function getColumnSqlDefinition(mixed $default = null, bool $isNullable = true): string
    {
        if (!$this->columnId) {
            throw new DatasetException("Cannot get SQL definition without column ID.");
        }

        $columnName = $this->getDatabaseColumnName();
        $sqlType = $this->getSqlType();
        $nullableClause = $isNullable ? 'NULL' : 'NOT NULL';
        $defaultClause = SqlHelper::formatDefaultValue($default);

        return "`{$columnName}` {$sqlType} {$nullableClause} DEFAULT {$defaultClause}";
    }

    /**
     * Verifying a specific value against its type
     *
     * @todo Use Nette validators instead?
     */
    public function isValidValue(mixed $value): bool
    {
        return match ($this->type) {
            'int' => is_int($value),
            'string', 'text', 'html', 'wysiwyg' => is_string($value),
            'bool' => is_bool($value),
            'json' => is_array($value) || is_object($value),
            default => false,
        };
    }

    /**
     * Prepare column data
     */
    public function prepare(): void
    {
        if (trim($this->name) !== '' && trim($this->slug) === '') {
            $this->slug = StringHelper::slugize($this->name);
        }
    }

    /**
     * Validates types and required values
     */
    public function validate(): void
    {
        if (!in_array($this->type, self::ALLOWED_TYPES, true)) {
            throw new DatasetException("Invalid data column type: '{$this->type}'.");
        }

        if (trim($this->name) === '') {
            throw new DatasetException("The column name must not be empty.");
        }

        if (trim($this->slug) === '') {
            throw new DatasetException("The column slug must not be empty.");
        }
    }

    public function setDatasetId(int $datasetId): self
    {
        $this->datasetId = $datasetId;
        return $this;
    }

    public function setColumnId(?int $columnId): self
    {
        $this->columnId = $columnId;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function setType(string $type = 'string'): self
    {
        $this->type = $type;
        return $this;
    }

    public function setRequired(bool $required = false): self
    {
        $this->required = $required;
        return $this;
    }

    public function setListed(bool $listed = false): self
    {
        $this->listed = $listed;
        return $this;
    }

    public function setHidden(bool $hidden = false): self
    {
        $this->hidden = $hidden;
        return $this;
    }

    public function setDeleted(bool $deleted = false): self
    {
        $this->deleted = $deleted;
        return $this;
    }

    public function setDefault(?string $default = null): self
    {
        if ($default === null) {
            $this->default = null;
        } else {
            $default = trim($default);
            $this->default = ($default === '') ? null : $default;
        }
        return $this;
    }
}
