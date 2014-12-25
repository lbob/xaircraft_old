<?php

namespace Xaircraft\Database;


/**
 * Class WhereQuery
 *
 * @package Xaircraft\Database
 * @author lbob created at 2014/12/23 14:27
 */
class WhereQuery {

    private $wheres = array();
    private $tableName;
    private $prefix;
    private $logicTableName;
    private $params = array();
    private $isSubQueryMode = false;

    public function __construct($tableName, $prefix)
    {
        if (!isset($tableName))
            throw new \InvalidArgumentException("Invalid table name");

        $this->logicTableName = $tableName;
        $this->prefix = $prefix;

        if (isset($this->prefix)) $this->tableName = $this->prefix . $tableName;
        else $this->tableName = $tableName;
    }

    /**
     * @return WhereQuery
     */
    public function where()
    {
        $args = func_get_args();
        $argsLen = func_num_args();
        $columnName = $args[0];
        if ($argsLen === 2) {
            $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', $columnName . ' = ? ');
            $this->params[] = $args[1];
        }
        if ($argsLen === 3) {
            $this->wheres[] = array(count($this->wheres) > 0 ? 'AND' : '', $columnName . ' ' . $args[1] . ' ? ');
            $this->params[] = $args[2];
        }

        return $this;
    }

    /**
     * @return WhereQuery
     */
    public function orWhere()
    {
        $args = func_get_args();
        $argsLen = func_num_args();
        $columnName = $args[0];
        if ($argsLen === 2) {
            $this->wheres[] = array(count($this->wheres) > 0 ? 'OR' : '', $columnName . ' = ? ');
            $this->params[] = $args[1];
        }
        if ($argsLen === 3) {
            $this->wheres[] = array(count($this->wheres) > 0 ? 'OR' : '', $columnName . ' ' . $args[1] . ' ? ');
            $this->params[] = $args[2];
        }

        return $this;
    }

    public function select()
    {
        $this->isSubQueryMode = true;
    }

    public function from($tableName)
    {
        $this->isSubQueryMode = true;


    }

    public function getQuery()
    {
        if (isset($this->wheres) && count($this->wheres) > 0) {
            $query[] = '(';
            if (isset($this->wheres) && count($this->wheres) > 0) {
                foreach ($this->wheres as $item) {
                    $query[] = implode(' ', $item);
                }
            }
            $query[] = ')';

            return implode(' ', $query);
        }

        return null;
    }

    public function getParams()
    {
        return $this->params;
    }
}

 