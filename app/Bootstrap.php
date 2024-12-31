<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;


class Bootstrap
{
    public static function boot(): Configurator
    {
        $configurator = new Configurator;
        $appDir = __DIR__ . '/..';

        //$configurator->setDebugMode('secret@23.75.345.200'); // enable for your remote IP
        $configurator->enableTracy("$appDir/log");
        // error_reporting(E_ERROR);

        $configurator->setTimeZone('Europe/Prague');
        $configurator->setTempDirectory("$appDir/temp");

        $configurator->createRobotLoader()
            ->addDirectory(__DIR__)
            ->register();

        foreach ([
            '/config/project/common.neon',
            '/config/project/services.neon',
            '/config/local.neon'
        ] as $path) {
            $configurator->addConfig($appDir . $path);
        }

        return $configurator;
    }
}
