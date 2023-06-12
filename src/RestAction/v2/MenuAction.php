<?php

namespace Lin3sWPRestApiMenus\RestAction\v2;

use Lin3sWPRestApiMenus\MenuItems;

final class MenuAction
{
    public function __invoke(\WP_REST_Request $request)
    {
        $id = $request->get_param('id');
        $locations = get_nav_menu_locations();

        if (!isset($locations[$id]) || $locations[$id] === 0) {
            return ['items' => []];
        }

        $term = get_term($locations[$id]);
        return [
            'items' => MenuItems::fromWPNavMenuItems(wp_get_nav_menu_items($term->term_id))
        ];
    }
}
