<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use App\Models\CategoryException;

class CategoryManager extends BaseModel
{
    public const TABLE_NAME = 'category';

    /** @todo Make this value configurable? */
    public const MAIN_CATEGORY_ID = 1;

    /** @var array<int,array<string,string|int|null>> $categories */
    protected array $categories = [];

    public function load(): void
    {
        if (empty($this->categories)) {
            $result = $this->db->table(self::TABLE_NAME)
                ->order('position')
                ->fetchAll();

            $this->categories = ArrayHelper::resultToArray($result);
        }
    }

    public function reload(): void
    {
        $this->invalidate();
        $this->load();
    }

    public function invalidate(): void
    {
        $this->categories = [];
    }

    /** @return array<string,mixed> */
    public function getById(int $id): array
    {
        $this->load();

        if (!isset($this->categories[$id])) {
            throw new CategoryException("Category ID '$id' not found.");
        }

        return $this->categories[$id];
    }

    /** @param array<string,string|int|null> $data */
    public function create(array $data): void
    {
        $data = CategoryValidator::prepareData($data);
        CategoryValidator::validateData($data);

        $this->db->table(self::TABLE_NAME)
            ->insert($data);

        $this->updateChildLevels();
        $this->invalidate();
    }

    /** @param array<string,string|int|null> $data */
    public function update(int $id, array $data): void
    {
        $this->load();

        if (!isset($this->categories[$id])) {
            throw new CategoryException("Category ID '$id' not found, entry cannot be updated", 1);
        }

        CategoryValidator::validateData($data);

        $this->db->table(self::TABLE_NAME)
            ->where(['id' => $id])
            ->update($data);

        $this->updateChildLevels();
        $this->invalidate();
    }

    public function delete(int $id): void
    {
        if ($id == 1) {
            throw new CategoryException('MAIN_CATEGORY cannot be removed.');
        }

        $node = $this->db->table(self::TABLE_NAME)
            ->get($id);

        if (!$node) {
            throw new CategoryException("Category ID '$id' not found.");
        }

        $parentID = $node['parent_id'];
        $node->delete();

        $this->db->table(self::TABLE_NAME)
            ->where(['parent_id' => $id])
            ->update(['parent_id' => $parentID]);

        $this->invalidate();
    }

    /** @return array<int,array<string,string|int|null>> */
    public function getData(): array
    {
        $this->load();
        return $this->categories;
    }

    /** @return list<array> */
    public function getTree(): array
    {
        $this->load();

        $items = $this->categories;
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
    public function getSortableTree(): array
    {
        $categoryTree = $this->getTree();
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

    /** @param array<mixed> $data */
    public function updatePosition(array $data): void
    {
        if (empty($data)) {
            throw new CategoryException('Data param is empty.');
        }

        ArrayHelper::assertMissingKeys(['node_id', 'source_id', 'target_id', 'order_list'], $data);

        // Update parent ID
        $this->db->table(self::TABLE_NAME)
            ->where(['id' => $data['node_id']])
            ->update(['parent_id' => $data['target_id']]);

        // Update positions
        $sql = "UPDATE `" . self::TABLE_NAME . "` SET `position` = CASE `id`\n";
        foreach ($data['order_list'] as $position => $id) {
            $sql .= "WHEN $id THEN $position\n";
        }
        $sql .= "ELSE `position` END\n";
        $sql .= "WHERE `id` IN (" . implode(',', $data['order_list']) . ");";

        $this->db->query($sql);

        // Update levels
        $this->updateChildLevels();

        $this->invalidate();
    }

    /**
     * Recursively updates the nesting level (`level`) of all child categories
     * based on the given parent category.
     *
     * @param int $parentId The ID of the parent category (default: `CategoryManager::MAIN_CATEGORY_ID`).
     * @param int $parentLevel The level of the parent category (default: 0).
     */
    public function updateChildLevels(int $parentId = self::MAIN_CATEGORY_ID, int $parentLevel = 0): void
    {
        $children = $this->db->table(self::TABLE_NAME)
            ->where('parent_id', $parentId)
            ->fetchPairs('id', 'level');

        foreach ($children as $childId => $_) {
            $newLevel = $parentLevel + 1;

            $this->db->table(self::TABLE_NAME)
                ->where('id', $childId)
                ->update(['level' => $newLevel]);

            $this->updateChildLevels($childId, $newLevel);
        }
    }

    /**
     * Returns a list of categories formatted for Nette form `addSelect()`,
     * using dashes (`— `) to visually indicate nesting levels.
     *
     * @return array<int,string> Array in the format [id => '— Category Name']
     */
    public function getCategorySelectData(): array
    {
        return $this->buildCategorySelectData($this->getTree());
    }

    /**
     * Recursively builds an array of categories with names
     * prefixed by dashes (`— `) to indicate hierarchical depth.
     *
     * @param list<array> $items Hierarchical category tree.
     * @param int $level The current nesting level (default: `0`).
     * @return array<int,string> Array in the format [id => '— Category Name'].
     */
    private function buildCategorySelectData(array $items, int $level = 0): array
    {
        $options = [];

        foreach ($items as $item) {
            $options[$item['id']] = str_repeat('— ', $level) . $item['name'];

            if (!empty($item['items'])) {
                $options += $this->buildCategorySelectData($item['items'], $level + 1);
            }
        }

        bdump($items);
        bdump($options);

        return $options;
    }
}
