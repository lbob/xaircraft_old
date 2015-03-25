<?php

namespace Xaircraft\Database;


/**
 * Class ColumnFormat
 *
 * @package Xaircraft\Database
 * @author lbob created at 2015/3/6 18:50
 */
class ColumnFormat {

    const DateTime = 1;
    const String = 2;
    const Integer = 3;
    const Float = 4;

    public static function getFormatValue($format, $value)
    {
        switch ($format) {
            case self::DateTime:
                return date("Y-m-d h:i:s", $value);
            case self::String:
                return $value . '';
            case self::Integer:
                return intval($value);
            case self::Float:
                return floatval($value);
            default:
                return $value;
        }
    }
}

 