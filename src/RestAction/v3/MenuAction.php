<?php

namespace Lin3sWPRestApiMenus\RestAction\v3;

use Lin3sWPRestApiMenus\MenuItems;

final class MenuAction
{
    public function __invoke(\WP_REST_Request $request)
    {
        $id = $request->get_param('id');
        $menu = wp_get_nav_menu_items($id);

        if (!$menu) {
            return ['items' => []];
        }

        $menu = [
            'slug'        => $id,
            'description' => wp_get_nav_menu_object($id)->name,
            'items'       => MenuItems::fromWPNavMenuItems($menu)];

        return apply_filters('rest_api_menus_after_build', $menu);
    }
}
