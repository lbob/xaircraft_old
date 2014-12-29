<?php

namespace Xaircraft\Common;


/**
 * Class Util
 *
 * @package Xaircraft\Common
 * @author lbob created at 2014/12/29 10:36
 */
class Util {

    public static function fast_array_filter($array, $pattern)
    {
        $pattern = '/' . preg_quote($pattern) . '/';
        return preg_grep($pattern, $array);
    }

    public static function fast_array_key_filter($array, $pattern)
    {
        $pattern = '/' . preg_quote($pattern) . '/';
        $keys = preg_grep($pattern, array_keys($array));
        $retArray = array_flip($keys);
        return array_intersect_key($array, $retArray);
    }
}

 