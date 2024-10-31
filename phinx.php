<?php

declare(strict_types=1);

$container = App\Bootstrap::boot()->createContainer();
$config = $container->getParameter('database');

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
            'host' => $config['host'],
            'name' => $config['name'],
            'user' => $config['user'],
            'pass' => $config['password'],
            'port' => '3306',
            'charset' => 'utf8',
        // ],
        // 'development' => [
        //     'adapter' => 'mysql',
        //     'host' => 'localhost',
        //     'name' => 'development_db',
        //     'user' => 'root',
        //     'pass' => '',
        //     'port' => '3306',
        //     'charset' => 'utf8',
        // ],
        // 'testing' => [
        //     'adapter' => 'mysql',
        //     'host' => 'localhost',
        //     'name' => 'testing_db',
        //     'user' => 'root',
        //     'pass' => '',
        //     'port' => '3306',
        //     'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];
