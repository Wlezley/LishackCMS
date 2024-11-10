#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$argcOffset = 2;
$commandList = [
    '--create' => 2,
    '--delete' => 1,
    '--disable' => 1,
    '--enable' => 1,
    '--rename' => 2,
    '--set-role' => 2,
];

if (isset($argv[1]) && in_array($argv[1], array_keys($commandList))) {
    $command = $argv[1];
    $argcRequired = $commandList[$command] + $argcOffset;
}

$container = App\Bootstrap::boot()->createContainer();
$manager = $container->getByType(App\Models\User::class);

switch ($command) {
    case '--create':
        if ($argc != $argcRequired) {
            echo 'Usage: user.php --create <username> <password>';
            exit(1);
        }

        $username = $argv[2];
        $password = $argv[3];

        try {
            $manager->createUser($username, $password);
            echo "User '$username' was added.\n";
        } catch (Nette\Security\AuthenticationException $e) {
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
        break;

    case '--delete':
        if ($argc != $argcRequired) {
            echo 'Usage: user.php --delete <username>';
            exit(1);
        }

        $username = $argv[2];

        try {
            $manager->deleteUser($username);
            echo "User '$username' was marked as deleted.\n";
        } catch (Nette\Security\AuthenticationException $e) {
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
        break;

    case '--disable':
        if ($argc != $argcRequired) {
            echo 'Usage: user.php --disable <username>';
            exit(1);
        }

        $username = $argv[2];

        try {
            $manager->disableUser($username);
            echo "User '$username' was disabled.\n";
        } catch (Nette\Security\AuthenticationException $e) {
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
        break;

    case '--enable':
        if ($argc != $argcRequired) {
            echo 'Usage: user.php --enable <username>';
            exit(1);
        }

        $username = $argv[2];

        try {
            $manager->enableUser($username);
            echo "User '$username' was enabled.\n";
        } catch (Nette\Security\AuthenticationException $e) {
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
        break;

    case '--rename':
        if ($argc != $argcRequired) {
            echo 'Usage: user.php --rename <old-name> <new-name>';
            exit(1);
        }

        $oldName = $argv[2];
        $newName = $argv[3];

        try {
            $manager->renameUser($oldName, $newName);
            echo "User '$oldName' was renamed to '$newName'.\n";
        } catch (Nette\Security\AuthenticationException $e) {
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
        break;

    case '--set-role':
        if ($argc != $argcRequired) {
            echo 'Usage: user.php --set-role <old-name> <new-name>';
            exit(1);
        }

        $username = $argv[2];
        $role = $argv[3];

        try {
            $manager->setUserRole($username, $role);
            echo "The role of user '$username' has been changed to '$role'.\n";
        } catch (Nette\Security\AuthenticationException $e) {
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
        break;

    default:
        echo '
=== LISHACK CMS :: USER MANAGER ===

Usage: user.php <command> [...]

Available comannds:
    --create <username> <password>      Create new user account
    --delete <username>                 Delete user account
    --disable <username>                Disable user account login
    --enable <username>                 Enable user account login
    --rename <old-name> <new-name>      Rename user account
    --set-role <username> <role>        Set role for user account
                                        Default roles: user, redactor, manager, admin, ...

';
        exit(0);
}
