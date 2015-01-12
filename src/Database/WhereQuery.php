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
    private $selectFields;
    private $subQueryTableName;

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
        if (func_num_args() > 0) {
            $this->selectFields = func_get_args();
        }

        return $this;
    }

    public function from($tableName)
    {
        $this->isSubQueryMode = true;
        if (isset($tableName)) {
            $this->subQueryTableName = $tableName;
        }

        return $this;
    }

    public function getQuery()
    {
        if ($this->isSubQueryMode && isset($this->subQueryTableName)) {
            $query[] = '(';

            $query[] = 'SELECT';
            if (isset($this->selectFields)) {
                $fields = array();
                foreach ($this->selectFields as $item) {
                    $fields[] = '`' . $item . '`';
                }
                $query[] = implode(',', $fields);
            } else {
                $query[] = '1';
            }

            if (isset($this->prefix)) {
                $query[] = 'FROM `' . $this->prefix . $this->subQueryTableName . '`';
            } else {
                $query[] = 'FROM `' . $this->subQueryTableName . '`';
            }

            $query[] = 'WHERE';

            if (isset($this->wheres) && count($this->wheres) > 0) {
                foreach ($this->wheres as $item) {
                    $query[] = implode(' ', $item);
                }
            }

            $query[] = ')';

            return implode(' ', $query);
        } else {
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
        }

        return null;
    }

    public function getParams()
    {
        return $this->params;
    }
}

 