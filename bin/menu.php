#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

define('DEFAULT_LANG', '');

$argcOffset = 2;
$commandList = [
    '--create' => 1,
    '--delete' => 1,
    '--list' => 0,
];

$command = '';
$argcRequired = 0;
if (isset($argv[1]) && in_array($argv[1], array_keys($commandList))) {
    $command = $argv[1];
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
        $parentID = $argv[3] ? (int)$argv[3] : NULL;

        try {
            $id = $manager->create([
                'name' => $name,
                'parent_id' => $parentID
            ]);
            echo "Menu '$name' was added [ID: $id].\n";
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
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
            echo "Error: " . $e->getMessage() . "\n";
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
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
        break;

    default:
        echo '
=== LISHACK CMS :: MENU MANAGER ===

Usage: menu.php <command> [...]

Available comannds:
    --create <name> [<parent_id>]       Create new menu item
    --delete <id>                       Delete menu item
    --list                              Show menu list

';
        exit(0);
}
