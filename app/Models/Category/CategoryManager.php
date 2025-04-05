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

    /**
     * Loads all categories from the database into internal cache.
     * Categories are indexed by their ID and ordered by `position`.
     */
    public function load(): void
    {
        if (empty($this->categories)) {
            $result = $this->db->table(self::TABLE_NAME)
                ->order('position')
                ->fetchAll();

            $this->categories = ArrayHelper::resultToArray($result);
        }
    }

    /**
     * Invalidates the category cache and reloads categories from the database.
     */
    public function reload(): void
    {
        $this->invalidate();
        $this->load();
    }

    /**
     * Clears the internal category cache.
     */
    public function invalidate(): void
    {
        $this->categories = [];
    }

    /**
     * Returns a category by its ID from the internal cache.
     *
     * @param int $id Category ID.
     * @return array<string,mixed> Category data.
     * @throws CategoryException If category is not found.
     */
    public function getById(int $id): array
    {
        $this->load();

        if (!isset($this->categories[$id])) {
            throw new CategoryException("Category ID '$id' not found.");
        }

        return $this->categories[$id];
    }

    /**
     * Creates a new category in the database.
     *
     * @param array<string,string|int|null> $data Data to insert (must pass validation).
     * @throws CategoryException If validation fails or DB insert fails.
     */
    public function create(array $data): void
    {
        $data = CategoryValidator::prepareData($data);
        CategoryValidator::validateData($data);

        $this->db->table(self::TABLE_NAME)
            ->insert($data);

        $this->invalidate();
        $this->updateChildLevels();
    }

    /**
     * Updates an existing category in the database.
     *
     * @param int $id Category ID to update.
     * @param array<string,string|int|null> $data New data (must pass validation).
     * @throws CategoryException If the category does not exist or validation fails.
     */
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

    /**
     * Deletes a category and updates related data:
     * - Moves child categories under the deleted category's parent.
     * - Moves related articles to the parent category.
     *
     * @param int $id Category ID to delete.
     * @throws CategoryException If MAIN_CATEGORY is being deleted or category doesn't exist.
     */
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

        // $this->articleManager->updateCategoryId($id, (int) $parentID);
        $this->db->table(ArticleManager::TABLE_NAME)
            ->where('category_id', $id)
            ->update(['category_id' => $parentID]);

        $this->invalidate();
        $this->updateChildLevels();
    }

    /**
     * Returns all cached categories indexed by ID.
     *
     * @return array<int,array<string,string|int|null>> Category data.
     */
    public function getData(): array
    {
        $this->load();
        return $this->categories;
    }

    /**
     * Builds and returns a hierarchical tree of categories.
     *
     * @return list<array> Nested category tree.
     */
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

    /**
     * Returns a list of active category IDs from a given category up to the root.
     *
     * @param int $activeCategory Category ID to trace.
     * @return array<int> List of category IDs (from current to top-level).
     * @throws CategoryException If any parent category is missing.
     */
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

    /**
     * Resolves a full category URL path (as list of slugs) into a category ID.
     *
     * @param array<string> $categoryUrlList List of category `name_url` parts (e.g. ['blog', 'tech']).
     * @return int Resolved category ID.
     * @throws CategoryException If path is invalid or category not found.
     */
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

    /**
     * Returns a nested tree of categories formatted for drag-and-drop sorting.
     *
     * @return list<array> Nested sortable category data.
     */
    public function getSortableTree(): array
    {
        $categoryTree = $this->getTree();
        return $this->sortableTreeFormat($categoryTree);
    }

    /**
     * Recursively formats a category tree for sorting purposes.
     * Builds a nested structure with metadata (id, name, url, etc.) for each node.
     *
     * @param list<array> $items List of category items (from getTree()).
     * @param string $url URL prefix built recursively (default: '').
     * @return list<array> Formatted tree suitable for sortable UI components.
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

    /**
     * Updates category positions and parent relationships based on drag-and-drop data.
     *
     * @param array<mixed> $data Reordering data, must contain keys:
     *   - node_id
     *   - source_id
     *   - target_id
     *   - order_list (ordered array of category IDs).
     *
     * @throws CategoryException If data is empty.
     * @throws \InvalidArgumentException If required keys are missing.
     */
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
     * Recursively updates the `level` field of child categories based on the parent category.
     *
     * @param int $parentId Parent category ID (default: MAIN_CATEGORY_ID).
     * @param int $parentLevel Nesting level of parent category (default: 0).
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
     * Returns a flat list of categories formatted for select boxes,
     * with dashes to indicate nesting depth.
     *
     * @return array<int,string> Format: [id => '— Category Name']
     */
    public function getCategorySelectData(): array
    {
        return $this->buildCategorySelectData($this->getTree());
    }

    /**
     * Helper for building a flat, indented category list from a nested tree.
     *
     * @param list<array> $items Category tree.
     * @param int $level Current nesting level (default: 0).
     * @return array<int,string> Format: [id => '— Category Name']
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
