<?php

namespace Xaircraft\Database;


/**
 * Interface Table
 *
 * @package Xaircraft\Database
 * @author lbob created at 2015/1/28 10:22
 */
interface Table {

    /**
     * 新建数据表
     * @param $name
     * @param $handler
     * @return Table
     */
    public function create($name, $handler);

    /**
     * 更新现有数据表
     * @param $name
     * @param $handler
     * @return Table
     */
    public function table($name, $handler);

    /**
     * 修改数据表名称
     * @param $from
     * @param $to
     * @return Table
     */
    public function rename($from, $to);

    /**
     * 删除数据表
     * @param $name
     * @return Table
     */
    public function drop($name);

    /**
     * 删除数据表
     * @param $name
     * @return Table
     */
    public function dropIfExists($name);

    /**
     * 对字段进行操作
     * @return Column
     */
    public function column();

    /**
     * 修改字段名称
     * @param $from
     * @param $to
     * @return Table
     */
    public function renameColumn($from, $to);

    /**
     * 删除数据表字段
     * @param string|array $nameOrNames
     * @return Table
     */
    public function dropColumn($nameOrNames);

    /**
     * 是否存在数据表
     * @param $name
     * @return boolean
     */
    public function hasTable($name);

    /**
     * 数据表是否存在字段
     * @param $tableName
     * @param $columnName
     * @return boolean
     */
    public function hasColumn($tableName, $columnName);

    /**
     * @return string
     */
    public function toString();

    /**
     * 执行命令
     * @return boolean
     */
    public function execute();
}

 