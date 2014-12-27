<?php

namespace Xaircraft\Common;


/**
 * Class IO
 *
 * @package Xaircraft\Common
 * @author lbob created at 2014/12/27 21:05
 */
class IO {
    public static function makeDir($dir)
    {
        if (isset($dir)) {
            if (stripos($dir, '\\') !== false) {
                $dir = str_replace('\\', '/', $dir);
            }
            $sections = explode('/', $dir);
            $path = array_shift($sections);
            foreach ($sections as $item) {
                $path .= '/' . $item;
                if (is_dir($path)) {
                    continue;
                } else {
                    mkdir($path);
                }
            }
            if (is_dir($dir)) {
                return $dir;
            }
            else {
                return false;
            }
        }
    }
}

 