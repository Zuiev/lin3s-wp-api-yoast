# lin3s-wp-rest-api-menus

Adds endpoints to Rest Api for WP menus



## Endpoints


* `/menus/v2/menus`: fetches a list of available menus
* `/menus/v2/menus?_embed`: fetches a list of available menus with it's items
* `/menus/v2/menus/{slug}`: returns menu of slug hierarchically 
* `/menus/v2/menus?page_hierarchy`: fetches a list of the pages with it's children if they have not been added to a menu

## Menu entries
Each item has this info exposed:
```
{
    "title": Menu entry title
    "slug": object slug. In case of custom link, url is provided instead
    "object_type": Type of post associated to menu entry (post, category, custom, page or posttype)
    "object_id": Id of associated post
    "menu_id": id of menu entry
    "parent_id": id of parent menu entry
    "children": array of child items
}
```
