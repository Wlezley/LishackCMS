<?php

declare(strict_types=1);

namespace App\Models\Dataset\Entity;

use App\Models\Helpers\StringHelper;

final class Dataset
{
    public ?int $id = null;
    public string $name = '';
    public string $slug = '';
    public string $component = '';
    public string $presenter = '';
    public bool $active = true;

    /**
     * Create an instance from the database record
     *
     * @param array<string,mixed> $row
     * @return Dataset
     */
    public static function fromDatabaseRow(array $row): self
    {
        $dataset = new self();
        $dataset->id = isset($row['id']) ? (int) $row['id'] : null;
        $dataset->name = (string) ($row['name'] ?? '');
        $dataset->slug = (string) ($row['slug'] ?? '');
        $dataset->component = (string) ($row['component'] ?? '');
        $dataset->presenter = (string) ($row['presenter'] ?? '');
        $dataset->active = (bool) ($row['active'] ?? true);

        return $dataset;
    }

    /**
     * Returns prepared data for database INSERT/UPDATE (without PK `id`)
     *
     * @return array<string,mixed>
     */
    public function toDatabaseRow(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'component' => $this->component,
            'presenter' => $this->presenter,
            'active' => $this->active,
        ];
    }

    public function prepare(): void
    {
        if (trim($this->name) !== '' && trim($this->slug) === '') {
            $this->slug = StringHelper::slugize($this->name);
        }
    }

    public function validate(): void
    {
        if (trim($this->name) === '') {
            throw new \InvalidArgumentException("The dataset name must not be empty.");
        }

        if (trim($this->slug) === '') {
            throw new \InvalidArgumentException("The dataset slug must not be empty.");
        }
    }



    public function setId(?int $id): self
    {
        $this->id = $id;
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

    public function setComponent(string $component): self
    {
        $this->component = $component;
        return $this;
    }

    public function setPresenter(string $presenter): self
    {
        $this->presenter = $presenter;
        return $this;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }
}
