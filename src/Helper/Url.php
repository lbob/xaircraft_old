<?php

namespace Xaircraft\Helper;


/**
 * Class Url
 *
 * @package Xaircraft\Helper
 * @author lbob created at 2014/12/7 20:24
 */
class Url
{

    public static function redirect($url, $params = null)
    {
        header('location:' . self::link($url, $params));
    }

    public static function link($url, $params = null)
    {
        if (stristr($url, 'http://') !== false) {
            return $url;
        } else {
            $router    = \Nebula\Router::getInstance();
            $routerUrl = $router->reverse($url, $params);
            if (isset($routerUrl))
                return \Xaircraft\App::getInstance()->environment[\Xaircraft\App::HOST] . $routerUrl;
            return \Xaircraft\App::getInstance()->environment[\Xaircraft\App::HOST];
        }
    }

    public static function redirectToRoute($routeName, $controller, $action, $params = null)
    {
        $router               = \Nebula\Router::getInstance();
        $params['controller'] = $controller;
        $params['action']     = $action;
        $url                  = $router->reverseByRoute($routeName, $params);
        if (isset($url))
            header(\Xaircraft\App::getInstance()->environment[\Xaircraft\App::HOST] . $url);
    }
}

 