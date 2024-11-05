<?php

declare(strict_types=1);

namespace App\Models;

use Nette;
use Nette\Database\Explorer;

class Config extends BaseModel
{
    public const TABLE_NAME = 'cms_config';

    private array $data = [];

    public function __construct(Explorer $db)
    {
        parent::__construct($db);

        $this->load();

        return $this->data;
    }

    public function load(): void
    {
        $selection = $this->db->table(self::TABLE_NAME);
        $result = $selection->fetchAll();

        foreach ($result as $row) {
            $item = $row->toArray();
            $this->data[$item['name']] = $item['value'];
        }
    }

    public function reload(): void
    {
        $this->data = [];
        $this->load();
    }

    public function update($param): void
    {
        foreach ($param as $name => $value) {
            $this->setValueByName($name, $value);
        }
    }

    public function setValueByName(string $name, string $value): void
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

    public function getValues(): array
    {
        return $this->data;
    }

    public function getValueByName(string $name): string
    {
        if (!empty($this->data[$name])) {
            return $this->data[$name];
        } else {
            return '';
        }
    }
}
