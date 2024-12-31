#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

const DEFAULT_LANG = "";

$argcOffset = 2;
$commandList = [
    '--create' => 2,
    '--delete' => 1,
    '--rename' => 2,
    '--set-role' => 2,
];

$command = $argv[1] ?? '';
$argcRequired = 0;
if (!empty($command) && in_array($command, array_keys($commandList))) {
    $argcRequired = $commandList[$command] + $argcOffset;
}

$container = App\Bootstrap::boot()->createContainer();
$manager = $container->getByType(App\Models\UserManager::class);

switch ($command) {
    case '--create':
        if ($argc != $argcRequired) {
            echo 'Usage: user.php --create <username> <password>';
            exit(1);
        }

        $username = (string)$argv[2];
        $password = (string)$argv[3];

        try {
            $id = $manager->create([
                'name' => $username,
                'password' => $password
            ]);
            echo "User '$username' was added [ID: $id].\n";
        } catch (\Exception $e) {
            printf("Error: %s\n", $e->getMessage());
            exit(1);
        }
        break;

    case '--delete':
        if ($argc != $argcRequired) {
            echo 'Usage: user.php --delete <id>';
            exit(1);
        }

        $id = (int)$argv[2];

        try {
            $manager->setDeleted($id, true);
            echo "User ID '$id' was marked as deleted.\n";
        } catch (\Exception $e) {
            printf("Error: %s\n", $e->getMessage());
            exit(1);
        }
        break;

    case '--rename':
        if ($argc != $argcRequired) {
            echo 'Usage: user.php --rename <id> <new-name>';
            exit(1);
        }

        $id = (int)$argv[2];
        $newName = (string)$argv[3];

        try {
            $manager->rename($id, $newName);
            echo "User ID '$id' was renamed to '$newName'.\n";
        } catch (\Exception $e) {
            printf("Error: %s\n", $e->getMessage());
            exit(1);
        }
        break;

    case '--set-role':
        if ($argc != $argcRequired) {
            echo 'Usage: user.php --set-role <id> <role>';
            exit(1);
        }

        $id = (int)$argv[2];
        $role = (string)$argv[3];

        try {
            $manager->setRole($id, $role);
            echo "The role of user ID '$id' has been changed to '$role'.\n";
        } catch (\Exception $e) {
            printf("Error: %s\n", $e->getMessage());
            exit(1);
        }
        break;

    default:
        echo <<< 'TEXT'
        === LISHACK CMS :: USER MANAGER ===

        Usage: user.php <command> [...]

        Available comannds:
            --create <username> <password>      Create new user account
            --delete <id>                       Delete user account
            --rename <id> <new-name>            Rename user account
            --set-role <id> <role>              Set role for user account
                                        Default roles: guest, user, redactor, manager, admin, ...
        TEXT, "\n";
        exit(0);
}
