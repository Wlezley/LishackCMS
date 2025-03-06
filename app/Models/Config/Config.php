<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Table\ActiveRow;
use App\Models\Helpers\ArrayHelper;
use App\Models\ConfigException;

class Config extends BaseModel
{
    public const TABLE_NAME = 'cms_config';

    /** @var array<string,array<string,mixed>> $data */
    protected mixed $data = [];

    public function load(): void
    {
        $result = $this->db->table(self::TABLE_NAME)
            ->fetchAll();

        /** @var ActiveRow $row */
        foreach ($result as $row) {
            $this->data[$row['category']][$row['name']] = $row['value'];
        }
    }

    // /** @param array<string,string> $param */
    // public function update(array $param): void
    // {
    //     foreach ($param as $name => $value) {
    //         $this->setValue($name, $value);
    //     }
    // }

    // public function setValue(string $name, string $value): void
    // {
    //     $selection = $this->db->table(self::TABLE_NAME)->where([
    //         'name' => $name
    //     ]);

    //     if ($selection->count() > 0) {
    //         $this->db->table(self::TABLE_NAME)->where([
    //             'name' => $name
    //         ])->update([
    //             'value' => $value
    //         ]);
    //     } else {
    //         $this->db->table(self::TABLE_NAME)->insert([
    //             'name' => $name,
    //             'value' => $value
    //         ]);
    //     }
    // }

    // public function getValue(string $name): string
    // {
    //     if (empty($this->data)) {
    //         $this->load();
    //     }

    //     return call_user_func_array('array_merge', array_values($this->data))[$name] ?? '';
    // }

    /** @return array<string,string> $param */
    public function getValues(bool $reload = false): array
    {
        if (empty($this->data) || $reload) {
            $this->load();
        }

        return call_user_func_array('array_merge', array_values($this->data));
    }

    // public function getValueByCategory(string $category, string $name): string
    // {
    //     if (empty($this->data)) {
    //         $this->load();
    //     }

    //     return $this->data[$category][$name] ?: '';
    // }

    // /** @return array<string,string> $param */
    // public function getValuesByCategory(string $category): array
    // {
    //     if (empty($this->data)) {
    //         $this->load();
    //     }

    //     return $this->data[$category] ?: [];
    // }
}
