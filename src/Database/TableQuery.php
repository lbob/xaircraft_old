<?php

namespace Xaircraft\Database;


/**
 * Class Query
 *
 * @package Xaircraft\Database
 * @author lbob created at 2014/12/19 10:05
 */
class TableQuery {

    const QUERY_SELECT = 'select';
    const QUERY_INSERT = 'insert';
    const QUERY_UPDATE = 'update';
    const QUERY_DELETE = 'delete';
    const QUERY_TRUNCATE = 'truncate';

    /**
     * @var Database
     */
    private $driver;
    private $primaryKey;
    private $tableName;
    private $wheres = array();
    private $params = array();
    private $queryType;
    private $selectFields = array();
    private $isLimited = false;
    private $limitStartIndex = 0;
    private $limitTakeLength = 0;
    private $isPaged = false;
    private $pageIndex = 0;
    private $pageSize = 0;
    private $orders = array();
    private $group;
    private $havings = array();

    public function __construct(Database $driver, $tableName, $primaryKey = null)
    {
        if (!isset($driver))
            throw new \InvalidArgumentException("Invalid database driver.");

        if (!isset($tableName))
            throw new \InvalidArgumentException("Invalid table name.");

        $this->driver = $driver;
        $this->tableName = $tableName;
        $this->primaryKey = $primaryKey;
    }

    /**
     * 执行并返回查询结果
     * @return mixed 返回查询结果
     */
    public function execute()
    {
        switch ($this->queryType) {
            case self::QUERY_SELECT:
                return $this->parseSelectQuery();
        }
    }

    private function parseSelectQuery()
    {
        $query[] = 'SELECT';
        if (isset($this->selectFields) && count($this->selectFields) > 0) {
            $query[] = implode(',', $this->selectFields);
        } else {
            $query[] = '*';
        }
        $query[] = 'FROM ' . $this->tableName;
        if ($this->isPaged) {
            $pageResult = $this->parsePageQuery($query);
            $queryResult = $this->driver->select($pageResult['query'], $this->params);
            return array(
                'recordCount' => $pageResult['recordCount'],
                'pageCount' => $pageResult['pageCount'],
                'data' => $queryResult
            );
        }
        $wheres = $this->parseWheres();
        if (isset($wheres)) {
            $query[] = $wheres;
        }
        if (isset($this->group)) {
            $query[] = 'GROUP BY ' . $this->group;
        }
        if (isset($this->orders) && count($this->orders) > 0) {
            $query[] = 'ORDER BY ' . implode(',', $this->orders);
        }
        if (!$this->isPaged && $this->isLimited) {
            $query[] = 'LIMIT ' . $this->limitStartIndex . ', ' . $this->limitTakeLength;
        }
        $query = implode(' ', $query);
        var_dump($query);
        return $this->driver->select($query, $this->params);
    }

    private function parsePageQuery($preQuery)
    {
        if (!isset($this->primaryKey))
            throw new \InvalidArgumentException("Page query must include primaryKey.");

        //取得分页结果的primaryKey值集合
        $query[] = 'SELECT';
        $query[] = 'COUNT(' . $this->primaryKey . ') AS TotalCount';
        $query[] = 'FROM';
        $query[] = $this->tableName;
        $wheres = $this->parseWheres();
        if (isset($wheres)) {
            $query[] = $wheres;
        }
        if (isset($this->group)) {
            $query[] = 'GROUP BY ' . $this->group;
        }
        $query = implode(' ', $query);
        $recordCount = 0;
        $recordCountResult = $this->driver->select($query, $this->params);
        foreach ($recordCountResult as $row) {
            $recordCount = $row['TotalCount'];
        }
        $pageCount = $recordCount % $this->pageSize == 0 ? $recordCount / $this->pageSize : intval($recordCount / $this->pageSize + 1);
        $this->pageIndex = $this->pageIndex > $pageCount ? $pageCount : $this->pageIndex;
        $query = array();
        $query[] = 'SELECT';
        $query[] = $this->primaryKey;
        $query[] = 'FROM';
        $query[] = $this->tableName;
        if (isset($wheres)) {
            $query[] = $wheres;
        }
        if (isset($this->group)) {
            $query[] = 'GROUP BY ' . $this->group;
        }
        if (isset($this->orders) && count($this->orders) > 0) {
            $query[] = 'ORDER BY ' . implode(',', $this->orders);
        }
        $limitStartIndex = ($this->pageIndex - 1) * $this->pageSize;
        $limitTakeLength = $this->pageSize;
        $query[] = 'LIMIT ' . $limitStartIndex . ', ' . $limitTakeLength;
        $query = implode(' ', $query);
        $primaryKeyValues = $this->driver->select($query, $this->params);
        $preQuery[] = 'WHERE ' . $this->primaryKey . ' IN (';
        $primaryKeyValueArray = array();
        foreach ($primaryKeyValues as $row) {
            $primaryKeyValueArray[] = $row[$this->primaryKey];
        }
        $preQuery[] = implode(',', $primaryKeyValueArray) . ')';
        if (isset($this->group)) {
            $preQuery[] = 'GROUP BY ' . $this->group;
        }
        if (isset($this->orders) && count($this->orders) > 0) {
            $preQuery[] = 'ORDER BY ' . implode(',', $this->orders);
        }
        $preQuery = implode(' ', $preQuery);
        return array(
            'query' => $preQuery,
            'recordCount' => $recordCount,
            'pageCount' => $pageCount
        );
    }

    private function parseWheres()
    {
        if (isset($this->wheres) && count($this->wheres) > 0) {
            $query[] = 'WHERE';
            foreach ($this->wheres as $item) {
                $query[] = implode(' ', $item);
            }
            return implode(' ', $query);
        }
        return null;
    }

    /**
     * 设置查询条件
     * @return TableQuery
     */
    public function where()
    {
        $args = func_get_args();
        $argsLen = func_num_args();
        if ($argsLen === 2) {
            $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', $args[0] . ' = ? ');
            $this->params[] = $args[1];
        }
        if ($argsLen === 3) {
            $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', $args[0] . ' ' . $args[1] . ' ? ');
            $this->params[] = $args[2];
        }

        return $this;
    }

    public function orWhere()
    {
        $args = func_get_args();
        $argsLen = func_num_args();
        if ($argsLen === 2) {
            $this->wheres[] = array(count($this->wheres) > 0 ? 'OR' : '', $args[0] . ' = ? ');
            $this->params[] = $args[1];
        }
        if ($argsLen === 3) {
            $this->wheres[] = array(count($this->wheres) > 0 ? 'OR' : '', $args[0] . ' ' . $args[1] . ' ? ');
            $this->params[] = $args[2];
        }

        return $this;
    }

    public function whereBetween($columnName, array $ranges)
    {
        if (count($ranges) === 2) {
            $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', '(' . $columnName . ' BETWEEN ? AND ?)');
            $this->params = array_merge($this->params, $ranges);
        }

        return $this;
    }

    public function whereNotBetween($columnName, array $ranges)
    {
        if (count($ranges) === 2) {
            $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', '(' . $columnName . ' < ? OR ' . $columnName . ' > ?)');
            $this->params = array_merge($this->params, $ranges);
        }

        return $this;
    }

    public function whereIn($columnName, array $ranges)
    {
        if (isset($ranges) && count($ranges) > 0) {
            $where = $columnName . ' IN (';
            $values = array();
            foreach ($ranges as $item) {
                $values[] = "?";
            }
            $where = $where . implode(',', $values) . ')';
            $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', $where);
            $this->params = array_merge($this->params, $ranges);
        }

        return $this;
    }

    public function whereNotIn($columnName, array $ranges)
    {
        if (isset($ranges) && count($ranges) > 0) {
            $where = $columnName . ' NOT IN (';
            $values = array();
            foreach ($ranges as $item) {
                $values[] = "?";
            }
            $where = $where . implode(',', $values) . ')';
            $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', $where);
            $this->params = array_merge($this->params, $ranges);
        }

        return $this;
    }

    public function orderBy($columnName, $order)
    {
        if (!isset($columnName))
            throw new \InvalidArgumentException("Invalid column name.");
        if (!isset($order) || !(strtolower($order) === 'desc' || strtolower($order) === 'asc'))
            throw new \InvalidArgumentException("Invalid order type.");

        $this->orders[] = $columnName . ' ' . $order;

        return $this;
    }

    public function groupBy()
    {
        $argsLen = func_num_args();
        if ($argsLen === 0)
            throw new \InvalidArgumentException("Invalid group by columns.");

        $this->group = implode(',', func_get_args());

        return $this;
    }

    public function having()
    {

    }

    /**
     * 设置获得查询结果的第一条记录
     * @return TableQuery
     */
    public function first()
    {
        $this->queryType = self::QUERY_SELECT;
        $this->isLimited = true;
        $this->limitStartIndex = 0;
        $this->limitTakeLength = 1;

        return $this;
    }

    /**
     * 设置获得查询结果的第一条记录的指定列的值
     * @param $columnName String 列名称
     * @return mixed TableQuery
     */
    public function pluck($columnName)
    {

    }

    /**
     * 设置去除重复记录
     * @return mixed TableQuery
     */
    public function distinct()
    {

    }

    /**
     * 设置返回的记录的列（可传入多个列名称）
     * @return TableQuery
     */
    public function select()
    {
        $this->queryType = self::QUERY_SELECT;
        if (func_num_args() > 0) {
            $this->selectFields = func_get_args();
        }
        return $this;
    }

    /**
     * 设置新增数据的查询
     * @param array $params 新增的字段/值数组
     * @return mixed TableQuery
     */
    public function insert(array $params)
    {

    }

    /**
     * 设置新增数据并返回自增ID的查询
     * @param array $params 新增的字段/值数组
     * @return mixed TableQuery
     */
    public function insertGetId(array $params)
    {

    }

    /**
     * 设置更新查询
     * @param array $params
     * @return mixed TableQuery
     */
    public function update(array $params)
    {

    }

    /**
     * 设置执行删除操作
     * @return mixed TableQuery
     */
    public function delete()
    {

    }

    /**
     * 设置清空数据表的查询
     * @return mixed TableQuery
     */
    public function truncate()
    {

    }

    /**
     * 设置查询跳过的记录条数
     * @param $count int 跳过的记录条数
     * @return TableQuery
     */
    public function skip($count)
    {
        $this->queryType = self::QUERY_SELECT;
        $this->isLimited = true;
        $this->limitStartIndex = $count;

        return $this;
    }

    /**
     * 设置返回的记录条数
     * @param $count int 返回的记录条数
     * @return TableQuery
     */
    public function take($count)
    {
        $this->queryType = self::QUERY_SELECT;
        $this->isLimited = true;
        $this->limitTakeLength = $count;

        return $this;
    }

    public function page($pageIndex, $pageSize)
    {
        $pageIndex = $pageIndex <= 0 ? 1 : $pageIndex;

        $this->queryType = self::QUERY_SELECT;
        $this->isPaged = true;
        $this->pageIndex = $pageIndex;
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * 设置连接查询
     * @param $tableName String 连接的表名称
     * @param $conditions String 连接条件
     * @return mixed TableQuery
     */
    public function join($tableName, $conditions)
    {

    }

    /**
     * 设置左连接查询
     * @param $tableName String 左连接的表名称
     * @param $conditions String 左连接条件
     * @return mixed TableQuery
     */
    public function leftJoin($tableName, $conditions)
    {

    }

    /**
     * 设置返回查询的记录条数
     * @return mixed TableQuery
     */
    public function count()
    {

    }

    /**
     * 设置查询某列的最大值
     * @param $columnName String 列名称
     * @return mixed TableQuery
     */
    public function max($columnName)
    {

    }

    /**
     * 设置查询某列的最小值
     * @param $columnName String 列名称
     * @return mixed TableQuery
     */
    public function min($columnName)
    {

    }

    /**
     * 设置查询某列的平均值
     * @param $columnName String 列名称
     * @return mixed TableQuery
     */
    public function avg($columnName)
    {

    }

    /**
     * 设置查询某列的值的总和
     * @param $columnName String 列名称
     * @return mixed TableQuery
     */
    public function sum($columnName)
    {

    }
}

 