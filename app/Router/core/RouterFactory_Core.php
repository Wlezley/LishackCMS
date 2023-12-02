<?php

// declare(strict_types=1);

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


abstract class RouterFactory_Core
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        // WEBSITE Router
        self::createWebsiteRouter($router);

        // ADMIN Router
        self::createAdminRouter($router);

        // // AJAX Router
        // self::createAjaxRouter($router);

        // // CRON Router
        // self::createCronRouter($router);

        return $router;
    }

    public static function createWebsiteRouter(RouteList $router): void
    {
        // $router->addRoute('a/<presenter>/<action>[/<id>]', 'Website:default');

        $router->withModule('Website')
            ->add(new Route('<presenter>/<action>[/<id>]', 'Website:default'))
            ->end();

        // return $router;
    }

    public static function createAdminRouter(RouteList $router): void
    {
        // $router->addRoute('admin/<presenter>/<action>[/<id>]', 'Admin:default');

        $router->withModule('Admin')
            ->add(new Route('admin/<presenter>/[<action>]', 'Admin:default'))
            ->end();

        // return $router;
    }

    // public static function createAjaxRouter(RouteList $router): void
    // {
    //     $router->addRoute('ajax/<presenter>/<action>[/<id>]', 'Ajax:default');
    //     return $router;
    // }

    // public static function createCronRouter(RouteList $router): void
    // {
    //     $router->addRoute('cron/<presenter>/<action>[/<id>]', 'Cron:default');
    //     return $router;
    // }
}
