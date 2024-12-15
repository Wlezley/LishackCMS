<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use App\Models\Helpers\StringHelper;
use Nette;
use Nette\Utils\Validators;

class MenuManager extends BaseModel
{
    public const TABLE_NAME = 'menu';

    /** @var array<int,array<string,string|int|null>> $data */
    protected mixed $data = [];

    public function load(): void
    {
        $result = $this->db->table(self::TABLE_NAME)
            ->order('position')
            ->fetchAll();

        $this->data = ArrayHelper::resultToArray($result);
    }

    /** @param array<string,string|int|null> $data */
    public function create(array $data): int
    {
        $data = $this->prepareData($data);
        $this->validateData($data);

        $id = $this->db->table(self::TABLE_NAME)
            ->insert($data);

        // @phpstan-ignore property.nonObject
        return $id->id;
    }

    /** @param array<string,string|int|null> $data */
    public function update(int $id, array $data): int
    {
        $this->validateData($data);

        $affectedRows = $this->db->table(self::TABLE_NAME)
            ->where(['id' => $id])
            ->update($data);

        return $affectedRows;
    }

    public function delete(int $id): void
    {
        if ($id == 1) {
            throw new \Exception("MAIN_MENU cannot be removed.");
        }

        $node = $this->db->table(self::TABLE_NAME)->get($id);
        if (!$node) {
            throw new \Exception("Menu item '$id' not found.");
        }

        $parentID = $node['parent_id'];
        $node->delete();

        $this->db->table(self::TABLE_NAME)
            ->where(['parent_id' => $id])
            ->update(['parent_id' => $parentID]);

        if (!empty($this->data)) {
            if (isset($this->data[$id])) {
                unset($this->data[$id]);
            }
            foreach ($this->data as $key => $item) {
                if ($item['parent_id'] == $id) {
                    $this->data[$key]['parent_id'] = $parentID;
                }
            }
        }
    }

    /** @return array<int,array<string,string|int|null>> */
    public function getData(bool $forceReload = false): array
    {
        if (empty($this->data) || $forceReload) {
            $this->load();
        }

        return $this->data;
    }

    /** @return list<array> */
    public function getTree(bool $forceReload = false): array
    {
        if (empty($this->data) || $forceReload) {
            $this->load();
        }

        $items = $this->data;
        $tree = [];

        foreach ($items as &$item) {
            if ($item['parent_id'] !== null && isset($items[$item['parent_id']])) {
                $items[$item['parent_id']]['items'][] = &$item;
            } else {
                $tree[] = &$item;
            }
        }

        return $tree;
    }

    /** @return list<array> */
    public function getSortableTree(bool $forceReload = false): array
    {
        $menuTree = $this->getTree($forceReload);
        return $this->sortableTreeFormat($menuTree);
    }

    /**
     * @param list<array> $items
     * @return list<array>
     */
    private function sortableTreeFormat(array $items, string $url = ''): array
    {
        $sortableTree = [];

        foreach ($items as $item) {
            $urlPath = $url . ($item['name_url'] ? $item['name_url'] . '/' : '');
            $sortableTree[] = [
                'data' => [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'name_url' => $item['name_url'],
                    'url' => '/' . $urlPath,
                    'hidden' => $item['hidden'],
                ],
                'nodes' => isset($item['items']) ? $this->sortableTreeFormat($item['items'], $urlPath) : [],
            ];
        }

        return $sortableTree;
    }

    public function updatePosition(mixed $data): void
    {
        if (!is_array($data)) {
            throw new MenuException('Param $data must be an array.', Nette\Http\IResponse::S406_NotAcceptable);
        }

        if (empty($data)) {
            throw new MenuException('Param $data is empty.', Nette\Http\IResponse::S404_NotFound);
        }

        ArrayHelper::assertMissingKeys(['node_id', 'source_id', 'target_id', 'order_list'], $data);

        // Update parent ID
        $this->db->table(self::TABLE_NAME)
            ->where(['id' => $data['node_id']])
            ->update(['parent_id' => $data['target_id']]);

        if (!empty($this->data)) {
            $this->data[$data['node_id']]['parent_id'] = (int)$data['target_id'];
        }

        // Update positions
        $sql = "UPDATE `menu` SET `position` = CASE `id`\n";
        foreach ($data['order_list'] as $position => $id) {
            $sql .= "    WHEN $id THEN $position\n";

            if (!empty($this->data)) {
                $this->data[$id]['position'] = $position;
            }
        }
        $sql .= "ELSE `position` END\n";
        $sql .= "WHERE `id` IN (" . implode(',', $data['order_list']) . ");";

        $this->db->query($sql);
    }

    /** @return array<string,string|int|null> */
    public function buildData(string $name, int $parentID = 1, int $position = 0, ?string $nameURL = null, ?string $title = null, ?string $description = null, ?string $body = null, bool $hidden = false): array
    {
        return [
            // 'id' => $id,
            'parent_id' => $parentID,
            'position' => $position,
            'name' => $name,
            'name_url' => $nameURL ?? StringHelper::webalize($name),
            'title' => $title,
            'description' => $description,
            'body' => $body,
            'hidden' => $hidden ? '1' : '0',
        ];
    }

    /**
     * @param array<string,string|int|null> $data
     * @return array<string,string|int|null>
     */
    public function prepareData(array $data): array
    {
        $name = $data['name'] ?? '';
        $nameURL = $data['name_url'] ?? (!empty($name) ? StringHelper::webalize($name) : '');

        return [
            // 'id' => $data['id'] ?? null,
            'parent_id' => $data['parent_id'] ?? 1,
            'position' => $data['position'] ?? 0,
            'name' => $name,
            'name_url' => $nameURL,
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'body' => $data['body'] ?? null,
            'hidden' => in_array($data['hidden'], ['0', '1'], true) ? $data['hidden'] : '0',
        ];
    }

    /** @param array<string,string|int|null> $data */
    private function validateData(array $data): void
    {
        ArrayHelper::assertExtraKeys(['id', 'parent_id', 'position', 'name', 'name_url', 'title', 'description', 'body', 'hidden'], $data);

        if (isset($data['id'])) {
            Validators::assert($data['id'], 'numericint', 'ID');
        }
        if (isset($data['parent_id'])) {
            Validators::assert($data['parent_id'], 'numericint', 'Parent ID');
        }
        if (isset($data['position'])) {
            Validators::assert($data['position'], 'numericint', 'Position');
        }
        if (isset($data['name'])) {
            Validators::assert($data['name'], 'string:1..255', 'Name');
        }
        if (isset($data['name_url'])) {
            Validators::assert($data['name_url'], 'string:1..255', 'Name URL');
            StringHelper::assertWebalized($data['name_url']);
        }
        if (isset($data['title'])) {
            Validators::assert($data['title'], 'string:1..255', 'Title');
        }
        if (isset($data['description'])) {
            Validators::assert($data['description'], 'string', 'Description');
        }
        if (isset($data['body'])) {
            Validators::assert($data['body'], 'string', 'Body');
        }
        if (isset($data['hidden']) && !in_array($data['hidden'], ['0', '1'], true)) {
            throw new \InvalidArgumentException('Hidden must be either "0" or "1".');
        }
    }
}
