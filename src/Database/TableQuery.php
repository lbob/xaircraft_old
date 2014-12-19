<?php

namespace Xaircraft\Database;


/**
 * Class Query
 *
 * @package Xaircraft\Database
 * @author lbob created at 2014/12/19 10:05
 */
interface TableQuery {

    /**
     * 执行并返回查询结果
     * @return mixed 返回查询结果
     */
    public function execute();

    /**
     * 设置查询条件
     * @param $conditions String 查询条件
     * @return mixed TableQuery
     */
    public function where($conditions);

    /**
     * 设置获得查询结果的第一条记录
     * @return mixed TableQuery
     */
    public function first();
    /**
     * 设置获得查询结果的第一条记录的指定列的值
     * @param $columnName String 列名称
     * @return mixed TableQuery
     */
    public function pluck($columnName);

    /**
     * 设置去除重复记录
     * @return mixed TableQuery
     */
    public function distinct();

    /**
     * 设置返回的记录的列（可传入多个列名称）
     * @return mixed TableQuery
     */
    public function select();

    /**
     * 设置新增数据的查询
     * @param array $params 新增的字段/值数组
     * @return mixed TableQuery
     */
    public function insert(array $params);

    /**
     * 设置新增数据并返回自增ID的查询
     * @param array $params 新增的字段/值数组
     * @return mixed TableQuery
     */
    public function insertGetId(array $params);

    /**
     * 设置更新查询
     * @param array $params
     * @return mixed TableQuery
     */
    public function update(array $params);

    /**
     * 设置删除表中所有数据查询
     * @return mixed TableQuery
     */
    public function delete();

    /**
     * 设置清空数据表的查询
     * @return mixed TableQuery
     */
    public function truncate();

    /**
     * 设置查询跳过的记录条数
     * @param $count int 跳过的记录条数
     * @return mixed TableQuery
     */
    public function skip($count);

    /**
     * 设置返回的记录条数
     * @param $count int 返回的记录条数
     * @return mixed TableQuery
     */
    public function take($count);

    /**
     * 设置连接查询
     * @param $tableName String 连接的表名称
     * @param $conditions String 连接条件
     * @return mixed TableQuery
     */
    public function join($tableName, $conditions);

    /**
     * 设置左连接查询
     * @param $tableName String 左连接的表名称
     * @param $conditions String 左连接条件
     * @return mixed TableQuery
     */
    public function leftJoin($tableName, $conditions);

    /**
     * 设置返回查询的记录条数
     * @return mixed TableQuery
     */
    public function count();

    /**
     * 设置查询某列的最大值
     * @param $columnName String 列名称
     * @return mixed TableQuery
     */
    public function max($columnName);

    /**
     * 设置查询某列的最小值
     * @param $columnName String 列名称
     * @return mixed TableQuery
     */
    public function min($columnName);

    /**
     * 设置查询某列的平均值
     * @param $columnName String 列名称
     * @return mixed TableQuery
     */
    public function avg($columnName);

    /**
     * 设置查询某列的值的总和
     * @param $columnName String 列名称
     * @return mixed TableQuery
     */
    public function sum($columnName);
}

 