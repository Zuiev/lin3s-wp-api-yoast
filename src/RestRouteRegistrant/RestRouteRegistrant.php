<?php

namespace Lin3sWPRestApiMenus\RestRouteRegistrant;

final class RestRouteRegistrant
{
    const VALID_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    static function register($prefix, $url, $methods, $callback)
    {
        if (!self::validateMethods($methods)) {
            throw new \Exception('Some of the given methods are invalid');
        }

        if (!is_callable($callback)) {
            throw new \Exception('Given callback is not callable');
        }

        register_rest_route( $prefix, $url, [
            'methods' => implode(', ', $methods),
            'callback' => $callback,
        ]);
    }

    static function validateMethods($methods) : bool
    {
        return array_reduce($methods, function($carry, $item) {
           return $carry && in_array($item, self::VALID_METHODS);
        }, true);
    }
}
