<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;

class Bootstrap
{
    public static function boot(): Configurator
    {
        $configurator = new Configurator();
        $appDir = dirname(__DIR__);

        match (getenv('APP_ENV')) {
            'dev',
            'local' => $configurator->setDebugMode(true),
            default => $configurator->setDebugMode(false), // production mode
        };

        $configurator->enableTracy($appDir . '/log');
        // error_reporting(E_ERROR);

        $configurator->setTimeZone('Europe/Prague');
        $configurator->setTempDirectory($appDir . '/temp');

        $configurator->createRobotLoader()
            ->addDirectory(__DIR__)
            ->register();

        $configurator->addConfig($appDir . '/config/project/common.neon');
        $configurator->addConfig($appDir . '/config/project/services.neon');
        $configurator->addConfig($appDir . '/config/local.neon');

        return $configurator;
    }
}
