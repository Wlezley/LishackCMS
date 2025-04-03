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

        $this->invalidate();
        $this->updateChildLevels();
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

        $this->invalidate();
        $this->updateChildLevels();
    }

    public function delete(int $id): void
    {
        if ($id == self::MAIN_CATEGORY_ID) {
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
        $this->updateChildLevels();
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

    /** @return array<int> */
    public function getActiveList(int $activeCategory): array
    {
        $this->load();

        $activeList = [];

        $limit = $this->categories[$activeCategory]['level'];
        $parent_id = $this->categories[$activeCategory]['id'];
        $activeList[] = (int) $parent_id;

        for ($level = 1; $level < $limit; $level++) {
            if (!isset($this->categories[$parent_id])) {
                throw new CategoryException("Category ID $parent_id not found.", 1);
            }

            $parent_id = $this->categories[$parent_id]['parent_id'];
            $activeList[] = (int) $parent_id;
        }

        return $activeList;
    }

    /** @param array<string> $categoryUrlList */
    public function resolveCategoryId(array $categoryUrlList): int
    {
        $this->load();

        $categoryId = self::MAIN_CATEGORY_ID;

        foreach ($categoryUrlList as $nameUrl) {
            $found = false;

            foreach ($this->categories as $category) {
                if ($category['name_url'] == $nameUrl && $category['parent_id'] == $categoryId) {
                    $found = true;
                    $categoryId = $category['id'];
                    break;
                }
            }

            if (!$found) {
                throw new CategoryException(
                    "Unable to find category ID for name_url: '$nameUrl' with parent_id: '$categoryId'.",
                    \Nette\Http\IResponse::S404_NotFound
                );
            }
        }

        return $categoryId;
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
        $this->load();

        $childs = [];
        foreach ($this->categories as $id => $category) {
            if ($category['parent_id'] == $parentId) {
                $childs[$id] = $category;
            }
        }

        foreach ($childs as $childId => $childCategory) {
            $newLevel = $parentLevel + 1;

            if ($childCategory['level'] != $newLevel) {
                $this->db->table(self::TABLE_NAME)
                    ->where('id', $childId)
                    ->update(['level' => $newLevel]);
            }
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

        return $options;
    }
}
