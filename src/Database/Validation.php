<?php

namespace Xaircraft\Database;


/**
 * Class Validation
 *
 * @package Xaircraft\Database
 * @author lbob created at 2014/12/26 16:50
 */
class Validation {

    private static $patternEnum = "#\'([^\']+)\'#i";
    private static $patternExpression = "#\<\!\-\-([A-Z]+)\:(.+?)\-\-\>#i";
    private static $patternNumberRange = "#\(([0-9\.]+),[ ]*([0-9\.]+)\)#i";

    private $columnName;
    private $type;
    private $isNullable = false;
    private $ranges;
    private $enums;
    private $pattern;

    public function __construct($columnName, $type, $isNullable, $enumDefine, $comment)
    {
        $this->columnName = $columnName;
        $this->type = $type;
        $this->isNullable = $isNullable;
        if (isset($enumDefine))
            $this->parseEnums($enumDefine);
        if (isset($comment))
            $this->parseComment($comment);
    }

    public function valid($value)
    {
        if (!$this->isNullable && is_null($value))
            return array(false, "can't be null");
        if ($this->type == 'integer' || $this->type == 'float' || $this->type == 'double') {
            if (isset($this->ranges)) {
                if ($value > $this->ranges['max'] || $value < $this->ranges['min'])
                    return array(false, "out of range : [$this->ranges['min']] - [$this->ranges['max']]");
            }
        }
        if ($this->type == 'string') {
            if (isset($this->ranges)) {
                $strLen = strlen($value);
                if ($strLen > $this->ranges['max'] || $strLen < $this->ranges['min'])
                    return array(false, "string length is out of range : [" . $this->ranges['min'] . "] - [" . $this->ranges['max'] . "]");
            }
            if (isset($this->pattern)) {
                if (!preg_match("#" . $this->pattern . "#i", $value))
                    return array(false, "string pattern invalid");
            }
        }
        return array(true, 'validation success');
    }

    private function parseEnums($enumDefine)
    {
        if (preg_match_all(self::$patternEnum, $enumDefine, $matches)) {
            $this->enums = $matches[1];
        }
    }

    private function parseComment($comment)
    {
        if (preg_match_all(self::$patternExpression, $comment, $matches)) {
            $matchedLength = count($matches);
            if ($matchedLength === 3) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    $settings[$matches[1][$i]] = $matches[2][$i];
                }
                $this->parseSettings($settings);
            }
        }
    }

    private function parseSettings($settings)
    {
        foreach ($settings as $key => $value) {
            switch (strtolower($key)) {
                case 'reg':
                    $this->pattern = $value;
                    break;
                case 'range':
                    $this->parseRanges($value);
                    break;
            }
        }
    }

    private function parseRanges($value)
    {
        if (preg_match(self::$patternNumberRange, $value, $matches)) {
            $min = $matches[1];
            $max = $matches[2];

            $this->ranges = array('min' => $min, 'max' => $max);
        }
    }
}

 