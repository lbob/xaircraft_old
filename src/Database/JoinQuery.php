<?php

namespace Xaircraft\Database;


/**
 * Class JoinQuery
 *
 * @package Xaircraft\Database
 * @author lbob created at 2014/12/23 10:17
 */
class JoinQuery {

    private $tableName;
    private $logicTableName;
    private $prefix;
    private $ons = array();
    private $wheres = array();
    private $params = array();
    private $isLeftJoin = false;

    public function __construct($tableName, $prefix, $isLeftJoin = false)
    {
        if (!isset($tableName))
            throw new \InvalidArgumentException("Invalid table name");

        $this->logicTableName = $tableName;
        $this->prefix = $prefix;
        $this->isLeftJoin = $isLeftJoin;

        if (isset($this->prefix)) $this->tableName = $this->prefix . $tableName;
        else $this->tableName = $tableName;
    }

    /**
     * @return JoinQuery
     */
    public function on()
    {
        $args = func_get_args();
        $argsLen = func_num_args();
        $columnName = stripos($args[0], '.') === false ? $this->tableName . '.' . $args[0] : $this->prefix . $args[0];
        if ($argsLen === 2) {
            $joinTableColumnName = stripos($args[1], '.') === false ? $args[1] : $this->prefix . $args[1];
            $this->ons[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'AND' : '', $columnName . ' = ' . $joinTableColumnName);
        }
        if ($argsLen === 3) {
            $joinTableColumnName = stripos($args[2], '.') === false ? $args[2] : $this->prefix . $args[2];
            $this->ons[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'AND' : '', $columnName . ' ' . $args[1] . ' ' . $joinTableColumnName);
        }

        return $this;
    }

    /**
     * @return JoinQuery
     */
    public function orOn()
    {
        $args = func_get_args();
        $argsLen = func_num_args();
        $columnName = stripos($args[0], '.') === false ? $this->tableName . '.' . $args[0] : $this->prefix . $args[0];
        if ($argsLen === 2) {
            $joinTableColumnName = stripos($args[1], '.') === false ? $args[1] : $this->prefix . $args[1];
            $this->ons[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'OR' : '', $columnName . ' = ' . $joinTableColumnName);
        }
        if ($argsLen === 3) {
            $joinTableColumnName = stripos($args[2], '.') === false ? $args[2] : $this->prefix . $args[2];
            $this->ons[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'OR' : '', $columnName . ' ' . $args[1] . ' ' . $joinTableColumnName);
        }

        return $this;
    }

    /**
     * @return JoinQuery
     */
    public function where()
    {
        $args = func_get_args();
        $argsLen = func_num_args();
        $columnName = stripos($args[0], '.') === false ? $this->tableName . '.' . $args[0] : $this->prefix . $args[0];
        if ($argsLen === 2) {
            $this->wheres[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'AND' : '', $columnName . ' = ? ');
            $this->params[] = $args[1];
        }
        if ($argsLen === 3) {
            $this->wheres[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'AND' : '', $columnName . ' ' . $args[1] . ' ? ');
            $this->params[] = $args[2];
        }

        return $this;
    }

    /**
     * @return JoinQuery
     */
    public function orWhere()
    {
        $args = func_get_args();
        $argsLen = func_num_args();
        $columnName = stripos($args[0], '.') === false ? $this->tableName . '.' . $args[0] : $this->prefix . $args[0];
        if ($argsLen === 2) {
            $this->wheres[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'OR' : '', $columnName . ' = ? ');
            $this->params[] = $args[1];
        }
        if ($argsLen === 3) {
            $this->wheres[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'OR' : '', $columnName . ' ' . $args[1] . ' ? ');
            $this->params[] = $args[2];
        }

        return $this;
    }

    public function getQuery()
    {
        $query[] = $this->isLeftJoin ? 'LEFT JOIN ' : 'JOIN ' . $this->tableName;
        if ((isset($this->ons) && count($this->ons) > 0) || (isset($this->wheres) && count($this->wheres) > 0)) {
            $query[] = 'ON (';
            if (isset($this->ons) && count($this->ons) > 0) {
                foreach ($this->ons as $item) {
                    $query[] = implode(' ', $item);
                }
            }
            if (isset($this->wheres) && count($this->wheres) > 0) {
                foreach ($this->wheres as $item) {
                    $query[] = implode(' ', $item);
                }
            }
            $query[] = ')';
        }

        return implode(' ', $query);
    }

    public function getParams()
    {
        return $this->params;
    }
}

 