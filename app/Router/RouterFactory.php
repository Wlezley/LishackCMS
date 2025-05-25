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
        self::createApiRouter($router);
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

    public static function createApiRouter(RouteList $router): void
    {
        $router->withModule('Api')
            ->addRoute('api/<action>[/<id>]', 'Api:default')
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
            ->addRoute('install/[<action>]', 'Install:default')

            // SEO (robots.txt, sitemap.xml)
            ->addRoute('robots.txt', 'Seo:robots')
            ->addRoute('sitemap.xml', 'Seo:sitemap')

            // Media
            ->addRoute('media/json/', 'Media:json')
            ->addRoute('media/<fileName>', 'Media:default')
            ->addRoute('media-<id>[-<width>[-<height>]]', [
                'presenter' => 'Media',
                'action' => 'show',
                'id' => 0,
                'width' => null,
                'height' => null,
            ])

            // ArticlePresenter
            ->addRoute('[[<categoryUrl [0-9a-zA-Z_\-\/]+>/]<articleUrl>/]', [
                'presenter' => 'Article',
                'action' => 'default',
                'categoryUrl' => null,
                'articleUrl' => null,
            ])
            ->end();
    }
}
