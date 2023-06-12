<?php

namespace Lin3sWPRestApiMenus\RestAction\v2;

use Lin3sWPRestApiMenus\MenuItems;

final class MenuListAction
{
    public function __invoke(\WP_REST_Request $request)
    {
        $locations = get_nav_menu_locations();
        $registeredMenus = get_registered_nav_menus();

        $locations = array_map(function ($slug, $description) use ($request, $locations) {
            $menu = [
                'slug'        => $slug,
                'description' => $description,
            ];

            if (!isset($request->get_params()['_embed'])) {
                return $menu;
            }

            if (!isset($locations[$slug]) || $locations[$slug] === 0) {
                return array_merge($menu, ['items' => []]);
            }

            $menuObj = get_term($locations[$slug]);

            return array_merge(
                $menu,
                ['items' => MenuItems::fromWPNavMenuItems(wp_get_nav_menu_items($menuObj->term_id))]
            );
        }, array_keys($registeredMenus), $registeredMenus);

        $locations = apply_filters('rest_api_menus_after_build', $locations);

        if (isset($request->get_params()['page_hierarchy']) && $request->get_params()['page_hierarchy'] === 'true') {
            return array_merge($locations, self::getPageHierarchy($locations));
        } else {
            return $locations;
        }
    }

    public function getPageHierarchy($locations)
    {
        $aResult = [];
        $args = [
            'sort_order'   => 'asc',
            'sort_column'  => 'menu_order, post_title',
            'hierarchical' => 1,
            'exclude'      => '',
            'include'      => '',
            'meta_key'     => '',
            'meta_value'   => '',
            'authors'      => '',
            'child_of'     => 0,
            'parent'       => -1,
            'exclude_tree' => '',
            'number'       => '',
            'offset'       => 0,
            'post_type'    => 'page',
            'post_status'  => 'publish',
        ];
        $items = get_pages($args);

        foreach ($items as $item) {
            $aItem = [
                'title'       => $item->post_title,
                'slug'        => MenuItems::getPostSlug($item),
                'object_type' => $item->post_type,
                'object_id'   => $item->ID,
                'parent_id'   => $item->post_parent,
                'items'       => [],
            ];

            if (isset($item->post_parent) && array_key_exists($item->post_parent, $aResult)) {
                $aResult[$item->post_parent]['items'][0]['items'][] = $aItem;
            } else {
                if ($item->post_parent === 0) {
                    if ($this->isItemOnMenu($item, $locations)) {
                        $aResult[$item->ID] = [
                            'slug'        => MenuItems::getPostSlug($item),
                            'description' => $item->post_title,
                            'items'       => [$aItem],
                        ];
                    }
                }
            }
        }

        return $aResult;
    }

    private function isItemOnMenu($item, $locations)
    {
        foreach ($locations as $location) {
            if (sizeof($location['items']) > 0) {
                foreach ($location['items'] as $menuItem) {
                    if ((int) $menuItem['object_id'] === $item->ID) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
