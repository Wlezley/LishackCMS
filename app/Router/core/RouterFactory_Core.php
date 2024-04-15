<?php

declare(strict_types=1);

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;


abstract class RouterFactory_Core
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        // ADMIN Router
        self::createAdminRouter($router);

        // AJAX Router
        self::createAjaxRouter($router);

        // CRON Router
        self::createCronRouter($router);

        // WEBSITE Router (must be last one)
        self::createWebsiteRouter($router);

        return $router;
    }

    public static function createAdminRouter(RouteList $router): void
    {
        $router->withModule('Admin')
            ->addRoute('admin/<presenter>/[<action>]', 'Admin:default')
            ->end();
    }

    public static function createAjaxRouter(RouteList $router): void
    {
        $router->withModule('Ajax')
            ->addRoute('ajax/<action>[/<id>]', 'Ajax:default')
            ->end();
    }

    public static function createCronRouter(RouteList $router): void
    {
        $router->withModule('Cron')
            ->addRoute('cron/<action>[/<id>]', 'Cron:default')
            ->end();
    }

    public static function createWebsiteRouter(RouteList $router): void
    {
        $router->withModule('Website')
            ->addRoute('<presenter>/<action>[/<id>]', 'Website:default')
            ->end();
    }
}
