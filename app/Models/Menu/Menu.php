<?php

namespace App\Models;


// Menu manager - Nested set model
class Menu extends BaseModel
{
    public const TABLE_NAME = 'menu';

    public function load(): void
    {
        $result = $this->db->table(self::TABLE_NAME)
            ->order('lft')
            ->fetchAll();

        $menuTree = [];
        $stack = [];

        foreach ($result as $node) {
            $item = [
                'id' => $node->id,
                'depth' => $node->depth, // TODO: Přejmenovat na 'level'?
                'name' => $node->name,
                'name_url' => $node->name_url,
                'title' => $node->title,
                'description' => $node->description,
                'body' => $node->body,
                'items' => []
            ];

            while (!empty($stack) && end($stack)['depth'] >= $node->depth) {
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

    public function getMenuTree(bool $forceReload = false): array
    {
        if (empty($this->data) || $forceReload) {
            $this->load();
        }

        return $this->data;
    }

    public function addMenuItem(string $title, int $parentId = NULL): int
    {
        if ($parentId === NULL) {
            $parentId = 1;
        }

        $parentNode = $this->db->table(self::TABLE_NAME)->get($parentId);
        if (!$parentNode) {
            throw new \Exception("Parent '$parentId' not found.");
        }

        // Aktualizace right hodnot pro přizpůsobení nové položky
        $left = $parentNode->rgt;
        $depth = $parentNode->depth + 1;

        $this->db->query('UPDATE menu SET rgt = rgt + 2 WHERE rgt >= ?', $left);
        $this->db->query('UPDATE menu SET lft = lft + 2 WHERE lft > ?', $left);

        // Vložení nové položky s definovaným left a right
        $result = $this->db->table(self::TABLE_NAME)->insert([
            'title' => $title,
            'parent_id' => (int)$parentId,
            'lft' => $left,
            'rgt' => $left + 1,
            'depth' => $depth,
        ]);

        return $result->id;
    }

    // Odstranění položky z menu včetně potomků
    public function removeMenuItem(int $id): void
    {
        if ($id == 1) {
            throw new \Exception("MAIN_MENU cannot be removed.");
        }

        // Načteme položku, kterou chceme smazat
        $node = $this->db->table(self::TABLE_NAME)->get($id);
        if (!$node) {
            throw new \Exception("Menu item '$id' not found.");
        }

        // Zjistíme rozsah hodnot left a right pro položku a její potomky
        $left = $node->lft;
        $right = $node->rgt;
        $width = $right - $left + 1;

        // Odstranění položky a všech jejích potomků
        $this->db->query('DELETE FROM ' . self::TABLE_NAME . ' WHERE lft BETWEEN ? AND ?', $left, $right);

        echo "$left to $right\n";

        // Úprava ostatních položek - snížení hodnot left a right tam, kde je to nutné
        $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET rgt = rgt - ? WHERE rgt > ?', $width, $right);
        $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET lft = lft - ? WHERE lft > ?', $width, $right);

        // Po odstranění znovu přeuspořádáme strom (možná nebude nutné, mělo by stačit přeskládat ostatní položky, viz. výše)
        // $this->reorderMenuTree();
    }

    // TODO: Přesunutí položky do jiného menu
    // public function moveMenuItem(int $id, int $newParentId): void
    // {
    //     // Načtení přesouvané položky
    //     $node = $this->db->table(self::TABLE_NAME)->get($id);
    //     if (!$node) {
    //         throw new \Exception("Menu item '$id' not found.");
    //     }

    //     // Načtení nového rodičovského uzlu
    //     $newParent = $this->db->table(self::TABLE_NAME)->get($newParentId);
    //     if (!$newParent) {
    //         throw new \Exception("New parent '$newParentId' not found.");
    //     }

    //     // Výpočet rozsahu uzlu, který budeme přesouvat
    //     $left = $node->lft;
    //     $right = $node->rgt;
    //     $width = $right - $left + 1;

    //     // Pokud je nový rodič potomkem přesouvaného uzlu, je pohyb neplatný
    //     if ($newParent->lft >= $left && $newParent->rgt <= $right) {
    //         throw new \Exception("Cannot move a node into one of its own descendants.");
    //     }

    //     // Dočasně "vytáhneme" přesouvaný uzel a jeho potomky z původní pozice
    //     $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET lft = lft - ?, rgt = rgt - ? WHERE lft BETWEEN ? AND ?', $left - 1, $left - 1, $left, $right);
    //     $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET lft = lft - ? WHERE lft > ?', $width, $right);
    //     $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET rgt = rgt - ? WHERE rgt > ?', $width, $right);

    //     // Nová hodnota left pro přesouvaný uzel
    //     $newLeft = $newParent->rgt;
    //     $newDepth = $newParent->depth + 1;

    //     // Úprava pozic ostatních položek na nové pozici
    //     $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET rgt = rgt + ? WHERE rgt >= ?', $width, $newLeft);
    //     $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET lft = lft + ? WHERE lft > ?', $width, $newLeft);

    //     // Aktualizace hodnot přesouvané položky a jejích potomků
    //     $depthDiff = $newDepth - $node->depth;
    //     $offset = $newLeft - $left;

    //     $this->db->query('UPDATE ' . self::TABLE_NAME . ' SET parent_id = ?, lft = lft + ?, rgt = rgt + ?, depth = depth + ? WHERE lft BETWEEN ? AND ?', $newParentId, $offset, $offset, $depthDiff, $left, $right);
    // }

    // TODO: Aktualizace uspořádání stromu
    // public function reorderMenuTree(): void
    // {
    //     // Načteme všechny uzly stromu seřazené dle `lft`
    //     $nodes = $this->db->table(self::TABLE_NAME)
    //         ->order('lft')
    //         ->fetchAll();

    //     $left = 1; // počáteční hodnota pro `lft`
    //     $stack = []; // zásobník pro sledování hloubky stromu

    //     foreach ($nodes as $node) {
    //         // Nastavíme počáteční hodnotu `lft`
    //         $this->db->table(self::TABLE_NAME)
    //             ->where('id', $node->id)
    //             ->update(['lft' => $left]);

    //         $left++;

    //         // Kontrolujeme, jestli jsme v hloubce podřízené položky
    //         while (!empty($stack) && end($stack)['right'] < $left) {
    //             array_pop($stack);
    //         }

    //         // Nastavíme hloubku položky dle počtu položek v zásobníku
    //         $depth = count($stack);
    //         $this->db->table(self::TABLE_NAME)
    //             ->where('id', $node->id)
    //             ->update(['depth' => $depth]);

    //         // Přidáme položku do zásobníku se záznamem o `right` hodnotě
    //         $stack[] = [
    //             'right' => $left + ($node->rgt - $node->lft - 1)
    //         ];

    //         // Nastavíme `rgt` hodnotu a zvýšíme `left`
    //         $this->db->table(self::TABLE_NAME)
    //             ->where('id', $node->id)
    //             ->update(['rgt' => $stack[count($stack) - 1]['right']]);

    //         $left = $stack[count($stack) - 1]['right'] + 1;
    //     }
    // }

}
