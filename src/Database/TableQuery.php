<?php

namespace Xaircraft\Database;
use Predis\Connection\ConnectionException;
use Xaircraft\App;
use Xaircraft\Log;


/**
 * Class Query
 *
 * @package Xaircraft\Database
 * @author lbob created at 2014/12/19 10:05
 */
class TableQuery
{

    const QUERY_SELECT = 'select';
    const QUERY_INSERT = 'insert';
    const QUERY_UPDATE = 'update';
    const QUERY_DELETE = 'delete';
    const QUERY_TRUNCATE = 'truncate';

    public $tableName;
    public $logicTableName;
    public $primaryKey;

    /**
     * @var Database
     */
    private $driver;
    private $prefix;
    private $wheres = array();
    private $whereParams = array();
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
    private $joins = array();
    private $joinParams = array();
    private $countColumnName;
    private $inserts;
    private $isInsertGetId = false;
    private $updates;
    private $formats;
    private $isCount = false;
    private $isPluck = false;
    private $isSingle = false;
    private $isRemeber = false;
    private $isDetail = false;
    private $remeberMinutes = 1;
    /**
     * @var \Xaircraft\Cache\CacheDriver
     */
    private $cacheDriver;

    /**
     * @var TableSchema
     */
    private $meta;

    public function __construct(Database $driver, $tableName, $prefix)
    {
        if (!isset($driver))
            throw new \InvalidArgumentException("Invalid database driver.");

        if (!isset($tableName))
            throw new \InvalidArgumentException("Invalid table name.");

        $this->driver         = $driver;
        $this->logicTableName = $tableName;
        $this->prefix         = $prefix;

        if (isset($this->prefix)) $this->tableName = $this->prefix . $tableName;
        else $this->tableName = $tableName;

        $this->meta = TableSchema::load($this->tableName);
        if (isset($this->meta)) {
            $this->primaryKey = isset($this->meta->primaryKey[0]) ? $this->meta->primaryKey[0] : null;
        }
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
            case self::QUERY_INSERT:
                return $this->parseInsertQuery();
            case self::QUERY_UPDATE:
                return $this->parseUpdateQuery();
            case self::QUERY_DELETE:
                return $this->parseDeleteQuery();
            case self::QUERY_TRUNCATE:
                return $this->parseTruncateQuery();
        }
    }

    private function getParams()
    {
        return array_merge($this->joinParams, $this->whereParams);
    }

    private function parseSelectQuery()
    {
        $query[] = 'SELECT';
        if (isset($this->countColumnName)) {
            $query[] = $this->countColumnName;
        } else {
            if (!empty($this->selectFields)) {
                $query[] = implode(',', $this->selectFields);
            } else {
                $query[] = '*';
            }
        }

        $query[] = 'FROM ' . $this->tableName;
        if (isset($this->joins) && count($this->joins) > 0) {
            foreach ($this->joins as $item) {
                $query[] = $item;
            }
        }
        if ($this->isPaged) {
            $pageResult  = $this->parsePageQuery($query);
            $queryResult = array();
            if ($pageResult['recordCount'] > 0) {
                $queryResult = $this->getSelectResult($pageResult['query'], $this->joinParams, true);
            }
            return array(
                'recordCount' => $pageResult['recordCount'],
                'pageCount'   => $pageResult['pageCount'],
                'data'        => $queryResult
            );
        }
        $wheres = $this->parseWheres();
        if (isset($wheres)) {
            $query[] = $wheres;
        }
        if (isset($this->group)) {
            $query[] = 'GROUP BY ' . $this->group;
        }
        $havings = $this->parseHavings();
        if (isset($havings)) {
            $query[] = $havings;
        }
        if (isset($this->orders) && count($this->orders) > 0) {
            $query[] = 'ORDER BY ' . implode(',', $this->orders);
        }
        if (!$this->isPaged && $this->isLimited) {
            $query[] = 'LIMIT ' . $this->limitStartIndex . ', ' . $this->limitTakeLength;
        }
        $query = implode(' ', $query);
        $result = $this->getSelectResult($query, $this->getParams(), true);
        if ($this->isCount) {
            return $result[0]['__TotalCount__'] + 0;
        }
        if ($this->isPluck) {
            if (isset($result[0]) && !empty($result[0])) {
                foreach ($result[0] as $key => $value) {
                    return $value;
                }
            }
            return null;
        }
        if ($this->isSingle) {
            if (count($this->selectFields) === 1) {
                $columnName = $this->selectFields[0];
                $subQueryPattern = '#[ ]+AS[ ]+([a-zA-Z][a-zA-Z0-9\_]*)#i';
                if (preg_match($subQueryPattern, $columnName, $matches)) {
                    if (count($matches) > 0) {
                        $columnName = $matches[1];
                    }
                } else {
                    $tableNameAndColumnPattern = '#([a-zA-Z][a-zA-Z0-9\_]*)\.([a-zA-Z][a-zA-Z0-9\_]*)#i';
                    if (preg_match($tableNameAndColumnPattern, $columnName, $matches)) {
                        if (count($matches) > 0) {
                            $columnName = $matches[2];
                        }
                    }
                }
                $data = array();
                if (isset($result)) {
                    foreach ($result as $row) {
                        $data[] = $row[$columnName];
                    }
                }
                $result = $data;
            }
        }
        if ($this->isDetail) {
            if (isset($result) && isset($result[0])) {
                $result = $result[0];
            }
        }
        return $result;
    }

    private function formatSelectResult($result)
    {
        if (!isset($this->formats) || empty($this->formats)) {
            return $result;
        }
        if (is_array($result)) {
            $formattedResult = array();
            foreach ($result as $row) {
                $formattedRow = array();
                foreach ($row as $key => $value) {
                    if (array_key_exists($key, $this->formats)) {
                        $formatValue = $this->formats[$key];
                        if (is_callable($formatValue)) {
                            $formattedRow[$key] = call_user_func($formatValue, $value);
                        } else {
                            $formattedRow[$key] = ColumnFormat::getFormatValue($this->formats[$key], $value);
                        }
                    } else {
                        $formattedRow[$key] = $value;
                    }
                }
                $formattedResult[] = $formattedRow;
            }
            $result = $formattedResult;
        }

        return $result;
    }

    private function getSelectResult($query, $params, $isFormat = false)
    {
        $result = array();
        if ($this->isRemeber && $this->isCanReadFromCached()) {
            $result = $this->readFromCache($query, $params);
            if (!isset($result)) {
                $result = $this->driver->select($query, $params);
                if ($isFormat) {
                    $result = $this->formatSelectResult($result);
                }
                $this->writeToCache($query, $params, $result);
            }
        } else {
            $result = $this->driver->select($query, $params);
            if ($isFormat) {
                $result = $this->formatSelectResult($result);
            }
        }
        return $result;
    }

    private function isCanReadFromCached()
    {
        $driver = App::get(\Xaircraft\Cache\CacheDriver::class);
        if (isset($driver)) {
            $this->cacheDriver = $driver;
            return true;
        } else {
            return false;
        }
    }

    private function readFromCache($query, array $params)
    {
        try {
            $key   = md5($query . implode(',', $params));
            $value = $this->cacheDriver->get($key);
            if (isset($value)) {
                return unserialize($value);
            } else {
                return null;
            }
        } catch (ConnectionException $ex) {
            Log::error('TableQuery', 'readFromCache error', array(
                'error_message' => $ex->getMessage(),
                'error_code' => $ex->getCode()
            ));
            return null;
        }
    }

    private function writeToCache($query, array $params, $result)
    {
        try {
            $key = md5($query . implode(',', $params));
            $this->cacheDriver->put($key, serialize($result), $this->remeberMinutes);
        } catch (ConnectionException $ex) {
            Log::error('TableQuery', 'readFromCache error', array(
                'error_message' => $ex->getMessage(),
                'error_code'    => $ex->getCode()
            ));
        }
    }

    private function parsePageQuery($preQuery)
    {
        if (!isset($this->primaryKey))
            throw new \InvalidArgumentException("Page query must include primaryKey.");

        $primaryKey = $this->primaryKey;
        if (isset($this->joins) && count($this->joins) > 0)
            $primaryKey = $this->tableName . '.' . $this->primaryKey;

        //取得分页结果的primaryKey值集合
        $query[] = 'SELECT';
        $query[] = 'COUNT(' . $primaryKey . ') AS TotalCount';
        $query[] = 'FROM';
        $query[] = $this->tableName;
        if (isset($this->joins) && count($this->joins) > 0) {
            foreach ($this->joins as $item) {
                $query[] = $item;
            }
        }
        $wheres = $this->parseWheres();
        if (isset($wheres)) {
            $query[] = $wheres;
        }
        if (isset($this->group)) {
            $query[] = 'GROUP BY ' . $this->group;
        }
        $havings = $this->parseHavings();
        if (isset($havings)) {
            $query[] = $havings;
        }
        $query             = implode(' ', $query);
        $recordCount       = 0;
        $recordCountResult = $this->getSelectResult($query, $this->getParams());
        foreach ($recordCountResult as $row) {
            $recordCount = $row['TotalCount'];
        }
        $pageCount       = $recordCount % $this->pageSize == 0 ? $recordCount / $this->pageSize
            : intval($recordCount / $this->pageSize + 1);
        $this->pageIndex = $this->pageIndex > $pageCount && $pageCount > 0 ? $pageCount : $this->pageIndex;
        $query           = array();
        $query[]         = 'SELECT';
        $query[]         = $primaryKey . ' AS ' . str_replace('.', '_', $primaryKey);
        $query[]         = 'FROM';
        $query[]         = $this->tableName;
        if (isset($this->joins) && count($this->joins) > 0) {
            foreach ($this->joins as $item) {
                $query[] = $item;
            }
        }
        if (isset($wheres)) {
            $query[] = $wheres;
        }
        if (isset($this->group)) {
            $query[] = 'GROUP BY ' . $this->group;
        }
        if (isset($havings)) {
            $query[] = $havings;
        }
        if (isset($this->orders) && count($this->orders) > 0) {
            $query[] = 'ORDER BY ' . implode(',', $this->orders);
        }
        $limitStartIndex      = ($this->pageIndex - 1) * $this->pageSize;
        $limitTakeLength      = $this->pageSize;
        $query[]              = 'LIMIT ' . $limitStartIndex . ', ' . $limitTakeLength;
        $query                = implode(' ', $query);
        $primaryKeyValues     = $this->getSelectResult($query, $this->getParams());
        if (!isset($primaryKeyValues) || empty($primaryKeyValues)) {
            return array(
                'query'       => $preQuery,
                'recordCount' => $recordCount,
                'pageCount'   => $pageCount
            );
        }
        $preQuery[]           = 'WHERE ' . $primaryKey . ' IN (';
        $primaryKeyValueArray = array();
        foreach ($primaryKeyValues as $row) {
            $primaryKeyValueArray[] = $row[str_replace('.', '_', $primaryKey)];
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
            'query'       => $preQuery,
            'recordCount' => $recordCount,
            'pageCount'   => $pageCount
        );
    }

    private function parseInsertQuery()
    {
        if (isset($this->inserts) && !empty($this->inserts)) {
            $query[] = 'INSERT INTO ' . $this->tableName;
            $query[] = '(`' . implode('`,`', array_keys($this->inserts)) . '`)';
            $query[] = 'VALUES';
            $query[] = '(';
            $paramsLen = count($this->inserts);
            $values = array();
            $inserts = array();
            foreach ($this->inserts as $key => $value) {
                if ($value instanceof Raw) {
                    $values[] = $value->getValue();
                } else {
                    $values[] = '?';
                    $inserts[] = $value;
                }
            }

            $query[] = implode(',', $values) . ')';
            $params = array_values($inserts);
            $query = implode(' ', $query);
            $isSuccess = $this->driver->insert($query, $params);
            if ($isSuccess && $this->isInsertGetId) {
                return $this->driver->getDbDriver()->lastInsertId();
            }
            return $isSuccess;
        }
        return false;
    }

    private function parseUpdateQuery()
    {
        if (isset($this->updates) && !empty($this->updates)) {
            $query[] = 'UPDATE ' . $this->tableName;
            $query[] = 'SET';
            $columns = array();
            foreach ($this->updates as $key => $value) {
                if ($value instanceof Raw) {
                    $columns[] = '`' . $key . '` = ' . $value->getValue();
                    unset($this->updates[$key]);
                } else {
                    $columns[] = '`' . $key . '` = ?';
                }
            }
            $query[] = implode(',', $columns);
            $wheres = $this->parseWheres();
            if (isset($wheres))
                $query[] = $wheres;
            $params = array_values($this->updates);
            $query = implode(' ', $query);
            return $this->driver->update($query, array_merge($params, $this->whereParams));
        }
        return false;
    }

    private function parseDeleteQuery()
    {
        $query[] = 'DELETE FROM ' . $this->tableName;
        $wheres = $this->parseWheres();
        if (isset($wheres))
            $query[] = $wheres;
        $query = implode(' ', $query);
        return $this->driver->delete($query, $this->whereParams);
    }

    private function parseTruncateQuery()
    {
        $query = 'TRUNCATE TABLE ' . $this->tableName;
        return $this->driver->statement($query);
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

    private function parseHavings()
    {
        if (isset($this->havings) && count($this->havings)) {
            $query[] = 'HAVING (';
            foreach ($this->havings as $item) {
                $query[] = implode(' ', $item);
            }
            return implode(' ', $query) . ')';
        }
        return null;
    }

    /**
     * 设置查询条件
     * @return TableQuery
     */
    public function where()
    {
        $args    = func_get_args();
        $argsLen = func_num_args();

        if ($argsLen === 1) {
            $handler = $args[0];
            if (is_callable($handler)) {
                $whereQuery = new WhereQuery($this->logicTableName, $this->prefix);
                call_user_func($handler, $whereQuery);
                $this->wheres[] = array(
                    count($this->wheres) > 0 ? 'AND' : '',
                    $whereQuery->getQuery()
                );
                $params         = $whereQuery->getParams();
                if (isset($params))
                    $this->whereParams = array_merge($this->whereParams, $params);
            }
        } else {
            $columnName = $args[0];
            if ($argsLen === 2) {
                if (is_a($args[1], Raw::RAW)) {
                    $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', $columnName . ' = ' . $args[1]->getValue());
                } else {
                    $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', $columnName . ' = ? ');
                    $this->whereParams[] = $args[1];
                }
            }
            if ($argsLen === 3) {
                if (is_a($args[2], Raw::RAW)) {
                    $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', $columnName . ' ' . $args[1] . ' ' . $args[2]->getValue());
                } else {
                    $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', $columnName . ' ' . $args[1] . ' ? ');
                    $this->whereParams[] = $args[2];
                }
            }
        }

        return $this;
    }

    public function orWhere()
    {
        $args    = func_get_args();
        $argsLen = func_num_args();

        if ($argsLen === 1) {
            $handler = $args[0];
            if (is_callable($handler)) {
                $whereQuery = new WhereQuery($this->logicTableName, $this->prefix);
                call_user_func($handler, $whereQuery);
                $this->wheres[] = array(
                    count($this->wheres) > 0 ? 'OR' : '',
                    $whereQuery->getQuery()
                );
                $params         = $whereQuery->getParams();
                if (isset($params))
                    $this->whereParams = array_merge($this->whereParams, $params);
            }
        } else {
            $columnName = $args[0];
            if ($argsLen === 2) {
                if (is_a($args[1], Raw::RAW)) {
                    $this->wheres[] = array(count($this->wheres) > 0 ? 'OR' : '', $columnName . ' = ' . $args[1]->getValue());
                } else {
                    $this->wheres[] = array(count($this->wheres) > 0 ? 'OR' : '', $columnName . ' = ? ');
                    $this->whereParams[] = $args[1];
                }
            }
            if ($argsLen === 3) {
                if (is_a($args[2], Raw::RAW)) {
                    $this->wheres[] = array(count($this->wheres) > 0 ? 'OR' : '', $columnName . ' ' . $args[1] . ' ' . $args[2]->getValue());
                } else {
                    $this->wheres[] = array(count($this->wheres) > 0 ? 'OR' : '', $columnName . ' ' . $args[1] . ' ? ');
                    $this->whereParams[] = $args[2];
                }
            }
        }

        return $this;
    }

    public function whereBetween($columnName, array $ranges)
    {
        if (count($ranges) === 2) {
            $this->wheres[]    = array(count($this->wheres) > 0 ? 'AND' : '', '(' . $columnName . ' BETWEEN ? AND ?)');
            $this->whereParams = array_merge($this->whereParams, $ranges);
        }

        return $this;
    }

    public function whereNotBetween($columnName, array $ranges)
    {
        if (count($ranges) === 2) {
            $this->wheres[]    = array(
                count($this->wheres) > 0 ? 'AND' : '',
                '(' . $columnName . ' < ? OR ' . $columnName . ' > ?)'
            );
            $this->whereParams = array_merge($this->whereParams, $ranges);
        }

        return $this;
    }

    public function whereIn($columnName, $params)
    {
        if (isset($params) && is_array($params)) {
            $ranges = $params;

            $where  = $columnName . ' IN (';
            $values = array();
            if (count($ranges) > 0) {
                foreach ($ranges as $item) {
                    $values[] = "?";
                }
            } else {
                $values[] = 'NULL';
            }
            $where             = $where . implode(',', $values) . ')';
            $this->wheres[]    = array(count($this->wheres) > 0 ? 'AND' : '', $where);
            $this->whereParams = array_merge($this->whereParams, $ranges);

        } else if (isset($params) && is_callable($params)) {
            $subQueryHandler = $params;
            $whereQuery      = new WhereQuery($this->logicTableName, $this->prefix);
            call_user_func($subQueryHandler, $whereQuery);
            $this->wheres[] = array(
                (count($this->wheres) > 0 ? 'AND ' : ' ') . $columnName . ' IN ',
                $whereQuery->getQuery()
            );
            $params         = $whereQuery->getParams();
            if (isset($params))
                $this->whereParams = array_merge($this->whereParams, $params);
        }

        return $this;
    }

    public function whereNotIn($columnName, $params)
    {
        if (isset($params) && is_array($params)) {
            $ranges = $params;
            if (isset($ranges) && count($ranges) > 0) {
                $where  = $columnName . ' NOT IN (';
                $values = array();
                foreach ($ranges as $item) {
                    $values[] = "?";
                }
                $where             = $where . implode(',', $values) . ')';
                $this->wheres[]    = array(count($this->wheres) > 0 ? 'AND' : '', $where);
                $this->whereParams = array_merge($this->whereParams, $ranges);
            }
        } else if (isset($params) && is_callable($params)) {
            $subQueryHandler = $params;
            $whereQuery = new WhereQuery($this->logicTableName, $this->prefix);
            call_user_func($subQueryHandler, $whereQuery);
            $this->wheres[] = array(
                (count($this->wheres) > 0 ? 'AND ' : ' ') . $columnName . ' NOT IN ',
                $whereQuery->getQuery()
            );
            $params         = $whereQuery->getParams();
            if (isset($params))
                $this->whereParams = array_merge($this->whereParams, $params);
        }

        return $this;
    }

    public function whereExists($subQueryHandler)
    {
        if (isset($subQueryHandler) && is_callable($subQueryHandler)) {
            $whereQuery = new WhereQuery($this->logicTableName, $this->prefix);
            call_user_func($subQueryHandler, $whereQuery);
            $this->wheres[] = array(
                (count($this->wheres) > 0 ? 'AND ' : ' ') . 'EXISTS',
                $whereQuery->getQuery()
            );
            $params         = $whereQuery->getParams();
            if (isset($params))
                $this->whereParams = array_merge($this->whereParams, $params);
        }

        return $this;
    }

    public function orWhereExists($subQueryHandler)
    {
        if (isset($subQueryHandler) && is_callable($subQueryHandler)) {
            $whereQuery = new WhereQuery($this->logicTableName, $this->prefix);
            call_user_func($subQueryHandler, $whereQuery);
            $this->wheres[] = array(
                (count($this->wheres) > 0 ? 'OR ' : ' ') . 'EXISTS',
                $whereQuery->getQuery()
            );
            $params         = $whereQuery->getParams();
            if (isset($params))
                $this->whereParams = array_merge($this->whereParams, $params);
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

        $columns = func_get_args();
        $this->group = implode(',', $columns);

        return $this;
    }

    public function having()
    {
        $args       = func_get_args();
        $argsLen    = func_num_args();
        $columnName = $args[0];
        if ($argsLen === 2) {
            $this->havings[]     = array(
                count($this->havings) > 0 ? 'AND' : '',
                $columnName . ' = ? '
            );
            $this->whereParams[] = $args[1];
        }
        if ($argsLen === 3) {
            $this->havings[]     = array(
                count($this->havings) > 0 ? 'AND' : '',
                $columnName . ' ' . $args[1] . ' ? '
            );
            $this->whereParams[] = $args[2];
        }

        return $this;
    }

    /**
     * 设置获得查询结果的第一条记录
     * @return TableQuery
     */
    public function first()
    {
        $this->queryType       = self::QUERY_SELECT;
        $this->isLimited       = true;
        $this->limitStartIndex = 0;
        $this->limitTakeLength = 1;

        return $this;
    }

    /**
     * 设置获得查询结果的第一条记录的指定列的值
     * @param $columnName String 列名称
     * @return TableQuery
     */
    public function pluck($columnName)
    {
        if (!isset($columnName))
            throw new \InvalidArgumentException("Invalid column name.");

        $this->queryType = self::QUERY_SELECT;
        $this->isPluck = true;
        $this->isLimited = true;
        $this->limitStartIndex = 0;
        $this->limitTakeLength = 1;
        $this->selectFields[] = $columnName;

        $this->isPaged = false;

        return $this;
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
        if (func_num_args() === 1) {
            $params = func_get_arg(0);
            if (isset($params) && is_array($params)) {
                $fields = array();
                foreach ($params as $key => $value) {
                    if (!is_string($key)) {
                        $fields[] = $value;
                    } else {
                        if (is_callable($value)) {
                            $subQueryHandler = $value;
                            $whereQuery = new WhereQuery($this->logicTableName, $this->prefix);
                            call_user_func($subQueryHandler, $whereQuery);
                            $fields[] = $whereQuery->getQuery() . ' AS ' . $key;
                            $params         = $whereQuery->getParams();
                            if (isset($params))
                                $this->whereParams = array_merge($this->whereParams, $params);
                        } else {
                            $fields[] = $value . ' AS ' . $key;
                        }
                    }
                }
                $this->selectFields = $fields;
            }
        }
        return $this;
    }

    public function single()
    {
        $this->isSingle = true;

        return $this;
    }

    /**
     * 设置新增数据的查询
     * @param array $params 新增的字段/值数组
     * @return TableQuery
     */
    public function insert(array $params)
    {
        if (!isset($params) || empty($params))
            throw new \InvalidArgumentException("Invalid insert params.");

        $this->queryType = self::QUERY_INSERT;
        $this->inserts = $params;

        return $this;
    }

    /**
     * 设置新增数据并返回自增ID的查询
     * @param array $params 新增的字段/值数组
     * @return TableQuery
     */
    public function insertGetId(array $params)
    {
        $this->insert($params);
        $this->isInsertGetId = true;

        return $this;
    }

    /**
     * 设置更新查询
     * @param array $params
     * @return TableQuery
     */
    public function update(array $params)
    {
        if (!isset($params) || empty($params))
            throw new \InvalidArgumentException("Invalid update params.");

        $this->queryType = self::QUERY_UPDATE;
        $this->updates = $params;

        return $this;
    }

    /**
     * 设置执行删除操作
     * @return TableQuery
     */
    public function delete()
    {
        $this->queryType = self::QUERY_DELETE;

        return $this;
    }

    /**
     * 设置清空数据表的查询
     * @return TableQuery
     */
    public function truncate()
    {
        $this->queryType = self::QUERY_TRUNCATE;

        return $this;
    }

    /**
     * 设置查询跳过的记录条数
     * @param $count int 跳过的记录条数
     * @return TableQuery
     */
    public function skip($count)
    {
        $this->queryType       = self::QUERY_SELECT;
        $this->isLimited       = true;
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
        $this->queryType       = self::QUERY_SELECT;
        $this->isLimited       = true;
        $this->limitTakeLength = $count;

        return $this;
    }

    public function page($pageIndex, $pageSize)
    {
        $pageIndex = !isset($pageIndex) || $pageIndex <= 0 ? 1 : $pageIndex;

        $this->queryType = self::QUERY_SELECT;
        $this->isPaged   = true;
        $this->pageIndex = $pageIndex;
        $this->pageSize  = $pageSize;

        return $this;
    }

    /**
     * 设置连接查询
     * @param $tableName String 连接的表名称
     * @return TableQuery
     */
    public function join($tableName)
    {
        if (!isset($tableName))
            throw new \InvalidArgumentException("Invalid table name");

        $args    = func_get_args();
        $argsLen = func_num_args();

        $joinQuery = new JoinQuery($tableName, $this->prefix);

        if ($argsLen === 2) {
            $handler = $args[1];
            if (isset($handler) && is_callable($handler)) {
                call_user_func($handler, $joinQuery);
            }
        }
        if ($argsLen === 3) {
            $joinQuery->on($args[1], $args[2]);
        }

        if ($argsLen === 4) {
            $joinQuery->on($args[1], $args[2], $args[3]);
        }

        $params        = $joinQuery->getParams();
        $this->joins[] = $joinQuery->getQuery();
        if (isset($params) && count($params) > 0)
            $this->joinParams = $params;

        return $this;
    }

    /**
     * 设置左连接查询
     * @param $tableName String 左连接的表名称
     * @return TableQuery
     */
    public function leftJoin($tableName)
    {
        if (!isset($tableName))
            throw new \InvalidArgumentException("Invalid table name");

        $args    = func_get_args();
        $argsLen = func_num_args();

        $joinQuery = new JoinQuery($tableName, $this->prefix, true);

        if ($argsLen === 2) {
            $handler = $args[1];
            if (isset($handler) && is_callable($handler)) {
                call_user_func($handler, $joinQuery);
            }
        }
        if ($argsLen === 3) {
            $joinQuery->on($args[1], $args[2]);
        }

        if ($argsLen === 4) {
            $joinQuery->on($args[1], $args[2], $args[3]);
        }

        $params        = $joinQuery->getParams();
        $this->joins[] = $joinQuery->getQuery();
        if (isset($params) && count($params) > 0)
            $this->joinParams = $params;

        return $this;
    }

    /**
     * 设置返回查询的记录条数
     * @return TableQuery
     */
    public function count()
    {
        $this->queryType = self::QUERY_SELECT;
        $this->isCount = true;
        $column          = '*';
        if (isset($this->primaryKey)) {
            $column = $this->primaryKey;
        }
        if (func_num_args() > 0) {
            $column = func_get_arg(0);
        }

        $this->countColumnName = 'COUNT(' . $column . ') AS __TotalCount__';
        return $this;
    }

    /**
     * @return TableSchema
     */
    public function getTableSchema()
    {
        return $this->meta;
    }

    public function remeber($minutes = 1)
    {
        $this->isRemeber = true;
        $this->remeberMinutes = $minutes;

        return $this;
    }

    /**
     * 设置返回结果集的列的类型，可设置类型有DateTime\Integer\Float\String三种
     * @param array $formats
     * @return TableQuery $this
     */
    public function format(array $formats = null)
    {
        $this->formats = $formats;

        return $this;
    }

    public function detail()
    {
        $this->isDetail = true;
        $this->isSingle = false;

        return $this;
    }
}

 