<?php

declare(strict_types=1);

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        self::createAdminRouter($router);
        self::createAjaxRouter($router);
        self::createCronRouter($router);
        self::createWebsiteRouter($router);

        return $router;
    }

    public static function createAdminRouter(RouteList $router): void
    {
        $router->withModule('Admin')
            ->addRoute('admin/login', 'Sign:in')
            ->addRoute('admin/logout', 'Sign:out')
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
            // ->addRoute('robots.txt', 'Robots:default')
            // ->addRoute('sitemap.xml', 'Sitemap:default')
            ->addRoute('[[<categoryUrl [0-9a-zA-Z_\-\/]+>/]<articleUrl>/]', [
                'presenter' => 'Article',
                'action' => 'detail',
                'categoryUrl' => null,
                'articleUrl' => null,
            ])
            ->end();
    }
}
