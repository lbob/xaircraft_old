<?php

namespace Xaircraft\Database;


/**
 * Class ColumnFormat
 *
 * @package Xaircraft\Database
 * @author lbob created at 2015/3/6 18:50
 */
class ColumnFormat {

    const DateTime = 'datetime';
    const String = 'string';
    const Integer = 'integer';
    const Float = 'float';
    const JsonObject = 'json_object';
    const JsonArray = 'json_array';

    public static function getFormatValue($format, $value)
    {
        switch ($format) {
            case self::DateTime:
                return date("Y-m-d H:i:s", $value);
            case self::String:
                return $value . '';
            case self::Integer:
                return intval($value);
            case self::Float:
                return floatval($value);
            case self::JsonObject:
                return json_decode($value);
            case self::JsonArray:
                return json_decode($value, true);
            default:
                return $value;
        }
    }
}

 