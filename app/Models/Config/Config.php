<?php

declare(strict_types=1);

namespace App\Models;

class Config extends BaseModel
{
    public const TABLE_NAME = 'cms_config';

    public function load(): void
    {
        $result = $this->db->table(self::TABLE_NAME)
            ->fetchAll();

        foreach ($result as $row) {
            $this->data[$row->category][$row->name] = $row->value;
        }
    }

    public function update(iterable $param): void
    {
        foreach ($param as $name => $value) {
            $this->setValue($name, $value);
        }
    }

    public function setValue(string $name, string $value): void
    {
        $selection = $this->db->table(self::TABLE_NAME)->where([
            'name' => $name
        ]);

        if ($selection->count() > 0) {
            $this->db->table(self::TABLE_NAME)->where([
                'name' => $name
            ])->update([
                'value' => $value
            ]);
        } else {
            $this->db->table(self::TABLE_NAME)->insert([
                'name' => $name,
                'value' => $value
            ]);
        }
    }

    public function getValue(string $name): string
    {
        return call_user_func_array('array_merge', array_values($this->data))[$name] ?? '';
    }

    public function getValues(): array
    {
        return call_user_func_array('array_merge', array_values($this->data));
    }

    public function getValueByCategory(string $category, string $name): string
    {
        return $this->data[$category][$name] ?? '';
    }

    public function getValuesByCategory(string $category): array
    {
        return $this->data[$category] ?? [];
    }
}
