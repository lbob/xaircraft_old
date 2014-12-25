<?php

namespace Xaircraft\Database;


/**
 * Class Database
 *
 * @package Xaircraft
 * @author lbob created at 2014/12/18 22:31
 */
interface Database {
    /**
     * 执行 Select 查询
     * @param $query String 查询语句
     * @return array 返回查询结果的数组
     */
    public function select($query, array $params = null);

    /**
     * 执行 Insert 查询
     * @param String $query 查询语句
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function insert($query, array $params = null);

    /**
     * 执行 Delete 查询
     * @param String $query 查询语句
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function delete($query, array $params = null);

    /**
     * 执行 Update 查询
     * @param String $query 查询语句
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function update($query, array $params = null);

    /**
     * 执行非CRUD操作
     * @param String $query 查询语句
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function statement($query, array $params = null);

    /**
     * 执行一个事务过程，在$handler中抛出异常则将自动执行回滚
     * @param callable $handler
     * @return mixed
     */
    public function transaction(callable $handler);

    /**
     * 手动开始一个事务过程
     * @return mixed
     */
    public function beginTransaction();

    /**
     * 手动回滚一个事务过程
     * @return mixed
     */
    public function rollback();

    /**
     * 手动提交事务查询
     * @return mixed
     */
    public function commit();

    /**
     * 禁用查询记录功能
     * @return mixed
     */
    public function disableQueryLog();

    /**
     * 获得查询语句
     * @return mixed
     */
    public function getQueryLog();

    /**
     * 建立一个连接
     * @param $dsn
     * @param $username
     * @param $password
     * @param $options
     * @return mixed
     */
    public function connection($dsn, $username, $password, $options, $prefix = null);

    /**
     * 关闭现有连接
     * @return mixed
     */
    public function disconnect();

    /**
     * 重新建立新的连接
     * @param $dsn
     * @param $username
     * @param $password
     * @param $options
     * @return mixed
     */
    public function reconnect($dsn, $username, $password, $options, $prefix = null);

    /**
     * 获得数据库驱动对象
     * @return \PDO 返回数据库驱动对象
     */
    public function getDbDriver();

    /**
     * @param null $name
     * @return mixed
     */
    public function lastInsertId($name = null);

    /**
     * 获得数据表查询对象
     * @param String $tableName 数据表名称
     * @return \Xaircraft\Database\TableQuery
     */
    public function table($tableName);

    /**
     * @param $query
     * @return \Xaircraft\ERM\Entity
     */
    public function entity($query);


    public function remeber($seconds);
}

 