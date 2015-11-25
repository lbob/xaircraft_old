<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/25
 * Time: 19:08
 */

namespace Xaircraft\Core;


class Strings
{
    public static function htmlFilter($str)
    {
        if (is_string($str) && is_null(json_decode($str))) {
            return htmlspecialchars($str);
        } else {
            return $str;
        }
    }

    public static function snakeToCamel($value)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
    }

    public static function camelToSnake($value)
    {
        return strtolower(preg_replace_callback('/([a-z])([A-Z])/', create_function('$match', 'return $match[1] . "_" . $match[2];'), $value));
    }
}