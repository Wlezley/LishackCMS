<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use App\Models\CategoryException;

class CategoryManager extends BaseModel
{
    public const TABLE_NAME = 'category';

    /** @var array<int,array<string,string|int|null>> $data */
    protected mixed $data = [];

    public function load(): void
    {
        $result = $this->db->table(self::TABLE_NAME)
            ->order('position')
            ->fetchAll();

        $this->data = ArrayHelper::resultToArray($result);
    }

    /** @return array<string,mixed> */
    public function get(int $id): array
    {
        $result = $this->db->table(self::TABLE_NAME)
            ->get($id);

        if (!$result) {
            throw new CategoryException("Category ID '$id' not found.");
        }

        return $result->toArray();
    }

    /** @param array<string,string|int|null> $data */
    public function create(array $data): int
    {
        $data = CategoryValidator::prepareData($data);
        CategoryValidator::validateData($data);

        $id = $this->db->table(self::TABLE_NAME)
            ->insert($data);

        // @phpstan-ignore property.nonObject
        return $id->id;
    }

    /** @param array<string,string|int|null> $data */
    public function update(int $id, array $data): int
    {
        CategoryValidator::validateData($data);

        $affectedRows = $this->db->table(self::TABLE_NAME)
            ->where(['id' => $id])
            ->update($data);

        return $affectedRows;
    }

    public function delete(int $id): void
    {
        if ($id == 1) {
            throw new CategoryException('MAIN_CATEGORY cannot be removed.');
        }

        $node = $this->db->table(self::TABLE_NAME)
            ->get($id);

        if (!$node) {
            throw new CategoryException("Category item ID '$id' not found.");
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
        $categoryTree = $this->getTree($forceReload);
        return $this->sortableTreeFormat($categoryTree);
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
            throw new CategoryException("Param 'data' must be an array.");
        }

        if (empty($data)) {
            throw new CategoryException("Param 'data' is empty.");
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
        $sql = "UPDATE `" . self::TABLE_NAME . "` SET `position` = CASE `id`\n";
        foreach ($data['order_list'] as $position => $id) {
            $sql .= "WHEN $id THEN $position\n";

            if (!empty($this->data)) {
                $this->data[$id]['position'] = $position;
            }
        }
        $sql .= "ELSE `position` END\n";
        $sql .= "WHERE `id` IN (" . implode(',', $data['order_list']) . ");";

        $this->db->query($sql);

        // Update levels
        $this->updateChildLevels(1, 0);
    }

    private function updateChildLevels(int $parentId, int $parentLevel): void
    {
        $children = $this->db->table(self::TABLE_NAME)
            ->where('parent_id', $parentId)
            ->fetchPairs('id', 'level');

        foreach ($children as $childId => $_) {
            $newLevel = $parentLevel + 1;

            $this->db->table(self::TABLE_NAME)
                ->where('id', $childId)
                ->update(['level' => $newLevel]);

            if (!empty($this->data)) {
                $this->data[$childId]['level'] = $newLevel;
            }

            $this->updateChildLevels($childId, $newLevel);
        }
    }
}
