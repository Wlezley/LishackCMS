#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

const DEFAULT_LANG = "";

$argcOffset = 2;
$commandList = [
    '--create' => 1,
    '--delete' => 1,
    '--list' => 0,
];

$command = $argv[1] ?? '';
$argcRequired = 0;
if (!empty($command) && in_array($command, array_keys($commandList))) {
    $argcRequired = $commandList[$command] + $argcOffset;
}

$container = App\Bootstrap::boot()->createContainer();
$manager = $container->getByType(App\Models\MenuManager::class);

switch ($command) {
    case '--create':
        if ($argc < $argcRequired) {
            echo 'Usage: menu.php --create <name> [<parent_id>]';
            exit(1);
        }

        $name = $argv[2];
        $parentID = (int)($argv[3] ?? 0);

        try {
            $id = $manager->create([
                'name' => $name,
                'parent_id' => $parentID
            ]);
            echo "Menu '$name' was added [ID: $id].\n";
        } catch (\Exception $e) {
            printf("Error: %s\n", $e->getMessage());
            exit(1);
        }
        break;

    case '--delete':
        if ($argc != $argcRequired) {
            echo 'Usage: menu.php --delete <id>';
            exit(1);
        }

        $id = (int)$argv[2];

        try {
            $manager->delete($id);
            echo "Menu item '$id' was removed.\n";
        } catch (\Exception $e) {
            printf("Error: %s\n", $e->getMessage());
            exit(1);
        }
        break;

    case '--list':
        if ($argc != $argcRequired) {
            echo 'Usage: user.php --list';
            exit(1);
        }

        try {
            $menuList = $manager->getTree();
            print_r($menuList);
        } catch (\Exception $e) {
            printf("Error: %s\n", $e->getMessage());
            exit(1);
        }
        break;

    default:
        echo <<< 'TEXT'
        === LISHACK CMS :: MENU MANAGER ===

        Usage: menu.php <command> [...]

        Available commands:
            --create <name> [<parent_id>]       Create new menu item
            --delete <id>                       Delete menu item
            --list                              Show menu list
        TEXT, "\n";
        exit(0);
}
