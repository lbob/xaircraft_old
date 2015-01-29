<?php

namespace Xaircraft\Database;


/**
 * Interface Column
 *
 * @package Xaircraft\Database
 * @author lbob created at 2015/1/28 11:25
 */
interface Column
{
    /**
     * @param $name
     * @return Column
     */
    public function increments($name);

    /**
     * @param $name
     * @return Column
     */
    public function bigIncrements($name);

    /**
     * @param $name
     * @return Column
     */
    public function bigInteger($name);

    /**
     * @param $name
     * @param $length
     * @return Column
     */
    public function binary($name, $length);

    /**
     * @param $name
     * @return Column
     */
    public function boolean($name);

    /**
     * @param $name
     * @return Column
     */
    public function char($name);

    /**
     * @param $name
     * @return Column
     */
    public function date($name);

    /**
     * @param $name
     * @return Column
     */
    public function dateTime($name);

    /**
     * @param $name
     * @param $precision int 尺度
     * @param $scale int 精度
     * @return Column
     */
    public function decimal($name, $precision, $scale);

    /**
     * @param $name
     * @param $precision int 尺度
     * @param $scale int 精度
     * @return Column
     */
    public function double($name, $precision, $scale);

    /**
     * @param $name
     * @param array $ranges 取值范围
     * @return Column
     */
    public function enum($name, array $ranges);

    /**
     * @param $name
     * @param $precision int 尺度
     * @param $scale int 精度
     * @return Column
     */
    public function float($name, $precision, $scale);

    /**
     * @param $name
     * @return Column
     */
    public function integer($name);

    /**
     * @param $name
     * @return Column
     */
    public function longText($name);

    /**
     * @param $name
     * @return Column
     */
    public function mediumInteger($name);

    /**
     * @param $name
     * @return Column
     */
    public function smallInteger($name);

    /**
     * @param $name
     * @return Column
     */
    public function tinyInteger($name);

    /**
     * 添加软删除字段，delete_at
     * @return Column
     */
    public function softDeletes();

    /**
     * @param $name
     * @param $length
     * @return Column
     */
    public function string($name, $length);

    /**
     * @param $name
     * @return Column
     */
    public function text($name);

    /**
     * @param $name
     * @return Column
     */
    public function time($name);

    /**
     * @return Column
     */
    public function nullable();

    /**
     * @param $value
     * @return Column
     */
    public function defaultValue($value);

    /**
     * @param $comment
     * @return Column
     */
    public function comment($comment);

    /**
     * @return Column
     */
    public function unsigned();

    /**
     * @param $name
     * @return Column
     */
    public function after($name);

    /**
     * @param $length
     * @return Column
     */
    public function length($length);

    /**
     * 加入索引
     * @return Column
     */
    public function unique();

    /**
     * 设为主键
     * @return Column
     */
    public function primaryKey();

    /**
     * @return string
     */
    public function toString();

    /**
     * @return boolean
     */
    public function isUnique();

    /**
     * @return boolean
     */
    public function isPrimaryKey();

    /**
     * @return string
     */
    public function getColumnName();

    /**
     * 设置类型
     * @param string $type
     * @return Column
     */
    public function setType($type);

    /**
     * 设置字段名称
     * @param $name
     * @return Column
     */
    public function setName($name);
}

 