<?php

namespace Xaircraft\Database;
use Xaircraft\DB;


/**
 * Class TempTableQuery
 *
 * @package Xaircraft\Database
 * @author skyweo created at 2015/7/14 0:16
 */
class TempTableQuery implements QueryStringBuilder {

    private $tempTableName;
    private $query;
    private $group;
    private $isLimited = false;
    private $limitTakeLength = 0;
    private $orders;
    private $selectFields = array();
    private $prefix;
    private $whereParams = array();
    private $wheres = array();
    private $params = array();

    public function __construct($tempTableName, callable $handler, $prefix = null)
    {
        $this->tempTableName = $tempTableName;
        $this->prefix = $prefix;

        if (isset($handler)) {
            $this->query = call_user_func($handler);
        }
    }

    public function execute()
    {
        $query = $this->parseQuery();
        var_dump($query);
        var_dump($this->params);
        return DB::select($query, $this->params);
    }

    private function parseQuery()
    {
        if ($this->query instanceof QueryStringBuilder) {
            $query = array();
            $query[] = $this->parseSelect();
            $queryString = $this->query->getQueryString();
            $queryParameters = $this->query->getQueryParameters();
            if (!empty($queryParameters)) {
                $this->params = array_merge($queryParameters, $this->params);
            }
            $query[] = '(' . $queryString . ') AS ' . $this->tempTableName;
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
            return implode(' ', $query);
        }
    }

    private function parseWheres(array $wheres = null)
    {
        if (empty($wheres)) {
            $wheres = $this->wheres;
        }
        if (isset($wheres) && count($wheres) > 0) {
            $query[] = 'WHERE';
            foreach ($wheres as $item) {
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

    private function parseSelect()
    {
        $query[] = 'SELECT';
        if (!empty($this->selectFields)) {
            $query[] = implode(',', $this->selectFields);
        } else {
            $query[] = '*';
        }

        $query[] = 'FROM ';

        return implode(' ', $query);
    }

    /**
     * @param $columnName
     * @param string $order
     * @return TempTableQuery
     */
    public function orderBy($columnName, $order = 'ASC')
    {
        if (!isset($columnName))
            throw new \InvalidArgumentException("Invalid column name.");
        if (!isset($order) || !(strtolower($order) === 'desc' || strtolower($order) === 'asc'))
            throw new \InvalidArgumentException("Invalid order type.");

        $this->orders[] = $columnName . ' ' . $order;

        return $this;
    }

    /**
     * @return TempTableQuery
     */
    public function select()
    {
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
                            $whereQuery      = new WhereQuery(null, $this->prefix);
                            call_user_func($subQueryHandler, $whereQuery);
                            $fields[] = $whereQuery->getQuery() . ' AS ' . $key;
                            $params   = $whereQuery->getParams();
                            $this->whereParams = array_merge($params, $this->whereParams);
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

    /**
     * @return TempTableQuery
     */
    public function where()
    {
        $args = func_get_args();
        $argsLen = func_num_args();
        $columnName = $args[0];
        if ($argsLen === 2) {
            if (is_a($args[1], Raw::RAW)) {
                $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', $columnName . ' = ' . $args[1]->getValue());
            } else {
                $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', $columnName . ' = ? ');
                $this->params[] = $args[1];
            }
        }
        if ($argsLen === 3) {
            if (is_a($args[2], Raw::RAW)) {
                $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', $columnName . ' ' . $args[1] . ' ' . $args[2]->getValue());
            } else {
                $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', $columnName . ' ' . $args[1] . ' ? ');
                $this->params[] = $args[2];
            }
        }

        return $this;
    }

    /**
     * @return TempTableQuery
     */
    public function groupBy()
    {
        $argsLen = func_num_args();
        if ($argsLen === 0)
            throw new \InvalidArgumentException("Invalid group by columns.");

        $columns     = func_get_args();
        $this->group = implode(',', $columns);

        return $this;
    }

    /**
     * @param $count
     * @return TempTableQuery
     */
    public function take($count)
    {
        $this->isLimited       = true;
        $this->limitTakeLength = $count;

        return $this;
    }

    public function getQueryString()
    {
        return $this->parseQuery();
    }

    /**
     * @return array
     */
    public function getQueryParameters()
    {
        return $this->params;
    }
}

