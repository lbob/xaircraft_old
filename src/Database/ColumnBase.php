<?php

namespace Xaircraft\Database;


/**
 * Class ColumnMySQL
 *
 * @package Xaircraft\Database
 * @author lbob created at 2015/1/28 11:27
 */
abstract class ColumnBase implements Column {

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

    protected $name;
    protected $type;
    protected $length;
    protected $isNullable = false;
    protected $defaultValue;
    protected $comment;
    protected $afterColumnName;
    /**
     * @var int 尺度
     */
    protected $precision;
    /**
     * @var int 精度
     */
    protected $scale;
    protected $enumRanges;
    protected $isAutoIncrement = false;
    protected $isUnsigned = false;
    protected $isUnique = false;
    protected $isPrimaryKey = false;

    /**
     * @param $name
     * @return Column
     */
    public function increments($name)
    {
        $this->name = $name;
        $this->isAutoIncrement = true;
        $this->type = $this->INTEGER;
        $this->length = 10;
        $this->defaultValue = 0;

        return $this;
    }

    /**
     * @param $name
     * @return Column
     */
    public function bigIncrements($name)
    {
        $this->name = $name;
        $this->isAutoIncrement = true;
        $this->type = $this->BIGINT;
        $this->length = 10;

        return $this;
    }

    /**
     * @return Column
     */
    public function unsigned()
    {
        if (!isset($this->type) || !isset($this->name) || !$this->isCanUnsigned($this->type)) {
            throw new \InvalidArgumentException("Can't define unsigned column which undefined name or type.");
        }

        $this->isUnsigned = true;

        return $this;
    }

    /**
     * @param $name
     * @return Column
     */
    public function bigInteger($name)
    {
        $this->name = $name;
        $this->type = $this->BIGINT;
        $this->length = 10;

        return $this;
    }

    /**
     * @param $name
     * @param $length
     * @return Column
     */
    public function binary($name, $length)
    {
        if (!isset($length) || $length <= 0) {
            throw new \InvalidArgumentException("Invalid argument [length] - $length");
        }

        $this->name = $name;
        $this->type = $this->BINARY;
        $this->length = $length;

        return $this;
    }

    /**
     * @param $name
     * @return Column
     */
    public function boolean($name)
    {
        $this->name = $name;
        $this->type = $this->BOOLEAN;
        $this->length = 1;

        return $this;
    }

    /**
     * @param $name
     * @return Column
     */
    public function char($name)
    {
        $this->name = $name;
        $this->type = $this->CHAR;

        return $this;
    }

    /**
     * @param $name
     * @return Column
     */
    public function date($name)
    {
        $this->name = $name;
        $this->type = $this->DATE;

        return $this;
    }

    /**
     * @param $name
     * @return Column
     */
    public function dateTime($name)
    {
        $this->name = $name;
        $this->type = $this->DATETIME;

        return $this;
    }

    /**
     * @param $name
     * @param $precision int 尺度
     * @param $scale int 精度
     * @return Column
     */
    public function decimal($name, $precision = null, $scale = null)
    {
        $this->name = $name;
        $this->type = $this->DECIMAL;
        if (isset($precision)) {
            $this->precision = $precision;
        }
        if (isset($scale)) {
            $this->scale = $scale;
        }

        return $this;
    }

    /**
     * @param $name
     * @param $precision int 尺度
     * @param $scale int 精度
     * @return Column
     */
    public function double($name, $precision, $scale)
    {
        $this->name = $name;
        $this->type = $this->DOUBLE;
        if (isset($precision)) {
            $this->precision = $precision;
        }
        if (isset($scale)) {
            $this->scale = $scale;
        }

        return $this;
    }

    /**
     * @param $name
     * @param array $ranges 取值范围
     * @return Column
     */
    public function enum($name, array $ranges)
    {
        if (!isset($ranges) || empty($ranges)) {
            throw new \InvalidArgumentException("Invalid ranges.");
        }

        $this->name = $name;
        $this->type = $this->ENUM;
        $this->enumRanges = $ranges;

        foreach ($ranges as $item) {
            if (!is_string($item)) {
                throw new \InvalidArgumentException("Invalid enum ranges: item must be string.");
            }
        }

        return $this;
    }

    /**
     * @param $name
     * @param $precision int 尺度
     * @param $scale int 精度
     * @return Column
     */
    public function float($name, $precision, $scale)
    {
        $this->name = $name;
        $this->type = $this->FLOAT;
        if (isset($precision)) {
            $this->precision = $precision;
        }
        if (isset($scale)) {
            $this->scale = $scale;
        }

        return $this;
    }

    /**
     * @param $name
     * @return Column
     */
    public function integer($name)
    {
        $this->name = $name;
        $this->type = $this->INTEGER;
        $this->length = 11;

        return $this;
    }

    /**
     * @param $name
     * @return Column
     */
    public function longText($name)
    {
        $this->name = $name;
        $this->type = $this->LONGTEXT;

        return $this;
    }

    /**
     * @param $name
     * @return Column
     */
    public function mediumInteger($name)
    {
        $this->name = $name;
        $this->type = $this->MEDIUMINT;
        $this->length = 9;

        return $this;
    }

    /**
     * @param $name
     * @return Column
     */
    public function smallInteger($name)
    {
        $this->name = $name;
        $this->type = $this->SMALLINT;
        $this->length = 6;

        return $this;
    }

    /**
     * @param $name
     * @return Column
     */
    public function tinyInteger($name)
    {
        $this->name = $name;
        $this->type = $this->TINYINT;
        $this->length = 1;

        return $this;
    }

    /**
     * 添加软删除字段，delete_at
     * @return Column
     */
    public function softDeletes()
    {
        $this->name = 'delete_at';
        $this->type = $this->INTEGER;
        $this->length = 10;
        $this->defaultValue = 0;

        return $this;
    }

    /**
     * @param $name
     * @param $length
     * @return Column
     */
    public function string($name, $length)
    {
        $this->name = $name;
        $this->type = $this->VARCHAR;
        $this->length = $length;

        return $this;
    }

    /**
     * @param $name
     * @return Column
     */
    public function text($name)
    {
        $this->name = $name;
        $this->type = $this->TEXT;

        return $this;
    }

    /**
     * @param $name
     * @return Column
     */
    public function time($name)
    {
        $this->name = $name;
        $this->type = $this->TIME;

        return $this;
    }

    /**
     * @return Column
     */
    public function nullable()
    {
        $this->isNullable = true;

        return $this;
    }

    /**
     * @param $value
     * @return Column
     */
    public function defaultValue($value)
    {
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * @param $comment
     * @return Column
     */
    public function comment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @param $name
     * @return Column
     */
    public function after($name)
    {
        $this->afterColumnName = $name;
    }

    /**
     * @param $length
     * @return Column
     */
    public function length($length)
    {
        if (!isset($this->type) || !isset($this->name) || !$this->isCanLength($this->type)) {
            throw new \InvalidArgumentException("Can't set length column which undefined name or type.");
        }

        $this->length = $length;

        return $this;
    }

    /**
     * 加入索引
     * @return Column
     */
    public function unique()
    {
        if (!isset($this->type) || !isset($this->name)) {
            throw new \InvalidArgumentException("Undefined name or type.");
        }

        $this->isUnique = true;

        return $this;
    }

    /**
     * 设为主键
     * @return Column
     */
    public function primaryKey()
    {
        if (!isset($this->type) || !isset($this->name)) {
            throw new \InvalidArgumentException("Undefined name or type.");
        }

        $this->isPrimaryKey = true;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isUnique()
    {
        return $this->isUnique;
    }

    /**
     * @return boolean
     */
    public function isPrimaryKey()
    {
        return $this->isPrimaryKey;
    }

    /**
     * @return string
     */
    public function getColumnName()
    {
        return $this->name;
    }

    /**
     * 设置类型
     * @param string $type
     * @return Column
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * 设置字段名称
     * @param $name
     * @return Column
     */
    public function setName($name)
    {
        if (!isset($name) && !is_string($name)) {
            throw new \InvalidArgumentException("Invalid name.");
        }

        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public abstract function toString();

    protected abstract function isCanUnsigned($type);

    protected abstract function isCanLength($type);

    protected abstract function isFloatNumber($type);
}

 