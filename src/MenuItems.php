<?php

namespace Lin3sWPRestApiMenus;

final class MenuItems
{
    public static function fromWPNavMenuItems($wpNavMenuItems) : array
    {
        return self::itemsToTree(self::transformItems($wpNavMenuItems));
    }

    private static function transformItems($items) : array
    {
        $parsedItems = [];

        foreach ($items as $item) {
            $parsedItems[] = [
                'title'       => $item->title,
                'slug'        => self::slugByObjectType($item->object_id, $item),
                'object_type' => $item->object,
                'object_id'   => $item->object_id,
                'menu_id'     => $item->ID,
                'parent_id'   => $item->menu_item_parent,
                'blank'       => '_blank' === $item->target,
            ];
        }

        return $parsedItems;
    }

    private static function slugByObjectType($id, $item)
    {
        switch ($item->type) {
            case 'taxonomy':
                $category = get_term($id);

                return $category->slug;

            case 'custom':
                return $item->url;

            default:
                $post = get_post($id);
                if (null !== $post) {
                    return self::getPostSlug($post);
                }
        }

        return '';
    }

    private static function itemsToTree($items)
    {
        if (!count($items)) {
            return [];
        }

        $itemsByParent = [];
        foreach ($items as $item) {
            $itemsByParent[$item['parent_id']][] = $item;
        }

        $treeBuilder = function ($siblings) use (&$treeBuilder, $itemsByParent) {
            foreach ($siblings as $key => $sibling) {
                $id = $sibling['menu_id'];
                if (isset($itemsByParent[$id])) {
                    $sibling['items'] = $treeBuilder($itemsByParent[$id]);
                }
                $siblings[$key] = $sibling;
            }

            return $siblings;
        };

        $tree = $treeBuilder(current($itemsByParent));

        return $tree;
    }

    public static function getPostSlug($post) : string
    {
        if ($post->post_parent !== 0) {
            return self::getPostSlug(get_post($post->post_parent)) . '/' . $post->post_name;
        }

        return '/' . $post->post_name;
    }
}
