<?php

namespace Xaircraft\Exception;


/**
 * Class ExceptionHelper
 *
 * @package Xaircraft\Exception
 * @author lbob created at 2015/3/19 10:17
 */
class ExceptionHelper {

    public static function ThrowIfNotTrue($boolean, $message = null)
    {
        if ($boolean) {
            return;
        }
        throw new \Exception($message);
    }

    public static function ThrowIfSpaceOrEmpty($string, $message = null)
    {
        if (!isset($string) || !is_string($string) || $string === '' || str_replace(' ', '', $string) === '') {
            throw new \Exception($message);
        }
    }

    public static function ThrowIfNullOrEmpty($value, $message = null)
    {
        if (!isset($value) || empty($value)) {
            throw new \Exception($message);
        }
    }
}

 