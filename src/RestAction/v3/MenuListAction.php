<?php

namespace Lin3sWPRestApiMenus\RestAction\v3;

use Lin3sWPRestApiMenus\MenuItems;

final class MenuListAction
{
    public function __invoke(\WP_REST_Request $request)
    {

        $defaultLanguage = null;
        $currentLanguage = null;

        if (function_exists('wpml_get_current_language') && function_exists('wpml_get_default_language')) {
            $defaultLanguage = wpml_get_default_language();
            $currentLanguage = wpml_get_current_language();
        }

        $menus = wp_get_nav_menus();

        $menus = array_filter($menus, function ($menu) use ($currentLanguage) {
            global $sitepress;

            $menu->laguageCode = null;
            if ($sitepress) {
                $menu->laguageCode = $sitepress->get_language_for_element($menu->term_taxonomy_id, 'tax_nav_menu');
            }

            static $idList = [];

            if ($menu->laguageCode && $menu->laguageCode !== $currentLanguage) {
                return false;
            }

            if (in_array($menu->slug, $idList)) {
                return false;
            }

            $idList[] = $menu->slug;

            return true;
        });


        $menus = array_map(function ($index, $menu) use ($request, $menus, $defaultLanguage, $currentLanguage) {
            $defaultLanguageMenu = null;

            if (function_exists('wpml_object_id_filter') && $defaultLanguage && $currentLanguage) {
                global $sitepress;

                $defaultLanguageMenuId = wpml_object_id_filter($menu->term_id, 'nav_menu', true, $defaultLanguage);

                $sitepress->switch_lang($defaultLanguage);
                $defaultLanguageMenu = wp_get_nav_menu_object($defaultLanguageMenuId);
                $sitepress->switch_lang($currentLanguage);
            }

            $newMenu = [
                'id'            => $menu->term_id,
                'original_slug' => $defaultLanguageMenu ? $defaultLanguageMenu->slug : $menu->slug,
                'slug'          => $menu->slug,
                'description'   => $menu->name,
                'lang'          => $menu->laguageCode,
            ];

            if (!isset($request->get_params()['_embed'])) {
                return $newMenu;
            }


            if (!isset($menus[$index]) || $menus[$index] === 0) {
                return array_merge($newMenu, ['items' => []]);
            }

            $menuObj = get_term($menus[$index]);

            return array_merge(
                $newMenu,
                ['items' => MenuItems::fromWPNavMenuItems(wp_get_nav_menu_items($menuObj->term_id))]
            );
        }, array_keys($menus), $menus);

        $menus = apply_filters('rest_api_menus_after_build', $menus);

        if (isset($request->get_params()['page_hierarchy']) && $request->get_params()['page_hierarchy'] === 'true') {
            return array_merge($menus, self::getPageHierarchy($menus));
        } else {
            return $menus;
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
