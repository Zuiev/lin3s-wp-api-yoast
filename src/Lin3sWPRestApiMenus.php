<?php

namespace Lin3sWPRestApiMenus;

use Lin3sWPRestApiMenus\RestAction\v2\MenuAction;
use Lin3sWPRestApiMenus\RestAction\v2\MenuListAction;
use Lin3sWPRestApiMenus\RestAction\v3\MenuAction as MenuByNameAction;
use Lin3sWPRestApiMenus\RestAction\v3\MenuListAction as MenuListByNameAction;
use Lin3sWPRestApiMenus\RestRouteRegistrant\RestRouteRegistrant;

final class Lin3sWPRestApiMenus
{
    const URL_PREFIX = 'menus/v2';
    const URL_PREFIX_V3 = 'menus/v3';
    const URL_BASE = '/menus';

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRestRoutes']);
    }

    public function registerRestRoutes()
    {
        RestRouteRegistrant::register(
            self::URL_PREFIX_V3,
            self::URL_BASE,
            ['GET'],
            new MenuListByNameAction
        );

        RestRouteRegistrant::register(
            self::URL_PREFIX_V3,
            self::URL_BASE . '/(?P<id>[a-zA-Z0-9_-]+)',
            ['GET'],
            new MenuByNameAction
        );

        RestRouteRegistrant::register(
            self::URL_PREFIX,
            self::URL_BASE,
            ['GET'],
            new MenuListAction
        );

        RestRouteRegistrant::register(
            self::URL_PREFIX,
            self::URL_BASE . '/(?P<id>[a-zA-Z0-9_-]+)',
            ['GET'],
            new MenuAction
        );
    }
}
