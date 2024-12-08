<?php

declare(strict_types=1);

$container = App\Bootstrap::boot()->createContainer();
$dbConfig = $container->getParameter('database');

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => '_phinxlog',
        'default_environment' => 'production',
        'production' => [
            'adapter' => 'mysql',
            'host' => $dbConfig['host'],
            'name' => $dbConfig['name'],
            'user' => $dbConfig['user'],
            'pass' => $dbConfig['password'],
            'port' => $dbConfig['port'],
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation',
    'templates' => [
        'style' => 'up_down'
    ]
];
