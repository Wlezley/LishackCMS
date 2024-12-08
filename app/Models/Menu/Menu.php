<?php

declare(strict_types=1);

namespace App\Models;

class Menu extends BaseModel
{
    public const TABLE_NAME = 'menu';

    /** @var list<array> $data */
    protected mixed $data = [];

    private UrlGenerator $urlGenerator;

    public function load(): void
    {
        $result = $this->db->table(self::TABLE_NAME)
            ->order('lft')
            ->fetchAll();

        $menuTree = [];
        $stack = [];

        foreach ($result as $node) {
            $item = $node->toArray();
            $item['items'] = [];

            while (!empty($stack) && end($stack)['depth'] >= $item['depth']) {
                array_pop($stack);
            }

            if (empty($stack)) {
                $menuTree[] = $item;
                $stack[] = &$menuTree[array_key_last($menuTree)];
            } else {
                $parent = &$stack[array_key_last($stack)];
                $parent['items'][] = $item;
                $stack[] = &$parent['items'][array_key_last($parent['items'])];
            }
        }

        $this->data = $menuTree;
    }

    /** @return list<array> */
    public function getMenuTree(bool $forceReload = false): array
    {
        if (empty($this->data) || $forceReload) {
            $this->load();
        }

        return $this->data;
    }

    public function addMenuItem(string $title, ?int $parentId = NULL): int
    {
        if ($parentId === NULL) {
            $parentId = 1;
        }

        $parentNode = $this->db->table(self::TABLE_NAME)->get($parentId);
        if (!$parentNode) {
            throw new \Exception("Parent '$parentId' not found.");
        }

        $left = $parentNode['rgt'];
        $depth = $parentNode['depth'] + 1;

        $this->db->query('UPDATE menu SET rgt = rgt + 2 WHERE rgt >= ?', $left);
        $this->db->query('UPDATE menu SET lft = lft + 2 WHERE lft > ?', $left);

        $result = $this->db->table(self::TABLE_NAME)->insert([
            'title' => $title,
            'parent_id' => $parentId,
            'lft' => $left,
            'rgt' => $left + 1,
            'depth' => $depth,
        ]);

        // @phpstan-ignore property.nonObject
        return $result->id;
    }

    public function removeMenuItem(int $id): void
    {
        if ($id == 1) {
            throw new \Exception("MAIN_MENU cannot be removed.");
        }

        $node = $this->db->table(self::TABLE_NAME)->get($id);
        if (!$node) {
            throw new \Exception("Menu item '$id' not found.");
        }

        $left = $node['lft'];
        $right = $node['rgt'];
        $width = $right - $left + 1;

        $this->db->query('DELETE FROM ' . self::TABLE_NAME . ' WHERE lft BETWEEN ? AND ?', $left, $right);
        $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET rgt = rgt - ? WHERE rgt > ?', $width, $right);
        $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET lft = lft - ? WHERE lft > ?', $width, $right);
    }

    public function setUrlGenerator(UrlGenerator $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function getUrlGenerator(): UrlGenerator
    {
        return $this->urlGenerator;
    }
}
