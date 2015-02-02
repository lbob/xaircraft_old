<?php

namespace Xaircraft\Database;


/**
 * Class ColumnMySQLImpl
 *
 * @package Xaircraft\Database
 * @author lbob created at 2015/1/28 19:54
 */
class ColumnMySQLImpl extends ColumnBase {

    protected $INTEGER = 'INT';
    protected $BINARY = 'BINARY';
    protected $BOOLEAN = 'TINYINT';
    protected $CHAR = 'CHAR';
    protected $DATE = 'DATE';
    protected $DATETIME = 'DATETIME';
    protected $TIME = 'TIME';
    protected $DECIMAL = 'DECIMAL';
    protected $DOUBLE = 'DOUBLE';
    protected $ENUM = 'ENUM';
    protected $FLOAT = 'FLOAT';
    protected $BIGINT = 'BIGINT';
    protected $LONGTEXT = 'LONGTEXT';
    protected $MEDIUMINT = 'MEDIUMINT';
    protected $MEDIUMTEXT = 'MEDIUMTEXT';
    protected $SMALLINT = 'SMALLINT';
    protected $TINYINT = 'TINYINT';
    protected $VARCHAR = 'VARCHAR';
    protected $TEXT = 'TEXT';

    /**
     * @return string
     */
    public final function toString()
    {
        if (!isset($this->name)) {
            throw new \InvalidArgumentException("Undefined column name.");
        }
        if (!isset($this->type)) {
            throw new \InvalidArgumentException("Undefined column type.");
        }

        $result = array();
        $result[] = "`" . $this->name . "`";
        $typeAndLength = $this->type;
        if ($this->isFloatNumber($this->type)) {
            if (isset($this->precision) && isset($this->scale)) {
                $typeAndLength = $typeAndLength . "($this->precision,$this->scale)";
                unset($this->length);
            }
        }
        if ($this->type === $this->ENUM) {
            $ranges = array();
            foreach ($this->enumRanges as $item) {
                $ranges[] = "'$item'";
            }
            $typeAndLength = $typeAndLength . "(" . implode(',', $ranges) . ")";
        }
        if (isset($this->length)) {
            $typeAndLength = $typeAndLength . "($this->length)";
        }
        $result[] = $typeAndLength;
        if ($this->isUnsigned && $this->isCanUnsigned($this->type)) {
            $result[] = 'UNSIGNED';
        }
        $result[] = $this->isNullable ? 'NULL' : 'NOT NULL';
        if ($this->isAutoIncrement) {
            $result[] = "AUTO_INCREMENT";
        }
        if (!$this->isAutoIncrement) {
            if (isset($this->defaultValue)) {
                $result[] = 'DEFAULT';
                if (is_string($this->defaultValue)) {
                    $result[] = "'$this->defaultValue'";
                } else {
                    $result[] = $this->defaultValue;
                }
            } else {
                if ($this->isNullable) {
                    $result[] = 'DEFAULT NULL';
                }
            }
        }
        if (isset($this->comment)) {
            $result[] = "COMMENT '$this->comment'";
        }
        if (isset($this->afterColumnName)) {
            $result[] = "AFTER `$this->afterColumnName`";
        }
        return implode(' ', $result);
    }

    protected final function isCanUnsigned($type)
    {
        switch (strtoupper($type)) {
            case $this->INTEGER:
            case $this->DOUBLE:
            case $this->FLOAT:
            case $this->TINYINT:
            case $this->SMALLINT:
            case $this->MEDIUMINT:
            case $this->BIGINT:
            case $this->BOOLEAN:
                return true;
            default:
                return false;
        }
    }

    protected final function isCanLength($type)
    {
        switch (strtoupper($type)) {
            default:
                return true;
        }
    }

    protected final function isFloatNumber($type)
    {
        switch (strtoupper($type)) {
            case $this->DOUBLE:
            case $this->FLOAT:
            case $this->DECIMAL:
                return true;
            default:
                return false;
        }
    }
}

 