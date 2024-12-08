#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$argcOffset = 2;
$commandList = [
    '--cleanup' => 0,
];

$command = '';
$argcRequired = 0;
if (isset($argv[1]) && in_array($argv[1], array_keys($commandList))) {
    $command = $argv[1];
    $argcRequired = $commandList[$command] + $argcOffset;
}

$container = App\Bootstrap::boot()->createContainer();
$tempDir = $container->getParameter('tempDir');

switch ($command) {
    case '--cleanup':
        if ($argc != $argcRequired) {
            echo 'Usage: cache.php --cleanup';
            exit(1);
        }

        if (!is_dir($tempDir)) {
            echo "Error: Directory '$tempDir' does not exist.\n";
            exit(1);
        }

        try {
            Nette\Utils\FileSystem::delete($tempDir . "/cache");
            echo "Cache has been cleaned.\n";
        } catch (Nette\IOException $e) {
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
        break;

    default:
        echo '
=== LISHACK CMS :: CACHE MANAGER ===

Usage: cache.php <command> [...]

Available comannds:
    --cleanup                           Cleanup Nette cache

';
        exit(0);
}
