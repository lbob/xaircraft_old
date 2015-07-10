<?php

namespace Xaircraft\Database;
use Xaircraft\DB;


/**
 * Class JoinQuery
 *
 * @package Xaircraft\Database
 * @author lbob created at 2014/12/23 10:17
 */
class JoinQuery {

    private $tableName;
    private $logicTableName;
    private $realTableName;
    private $anotherName;
    private $prefix;
    private $ons = array();
    private $wheres = array();
    private $params = array();
    private $isLeftJoin = false;
    private $isSoftDeleted = false;
    private $isSoftDeleteLess = false;

    /**
     * @var TableSchema
     */
    private $meta;

    public function __construct($tableName, $prefix, $isLeftJoin = false)
    {
        if (!isset($tableName))
            throw new \InvalidArgumentException("Invalid table name");

        $this->logicTableName = $tableName;
        $this->prefix = $prefix;
        $this->isLeftJoin = $isLeftJoin;

        if (preg_match(TableQuery::TABLE_NAME_PATTERN, $tableName, $matches)) {
            $this->realTableName = $matches['realName'];
            if (array_key_exists('anotherName', $matches)) {
                $this->anotherName = $matches['anotherName'];
            }
        }

        if (isset($this->prefix)) {
            $this->tableName = $this->prefix . $tableName;
            $this->realTableName = $this->prefix . $this->realTableName;
        }
        else $this->tableName = $tableName;

        $this->meta = TableSchema::load($this->realTableName);
        if (isset($this->meta)) {
            $fields = $this->meta->getFields();
            if (array_search(TableQuery::SoftDeletedColumnName, $fields)) {
                $this->isSoftDeleted = true;
            }
        }
    }

    /**
     * @return JoinQuery
     */
    public function on()
    {
        $args = func_get_args();
        $argsLen = func_num_args();
        $columnName = $args[0];
        if ($argsLen === 2) {
            $joinTableColumnName = $args[1];
            $this->ons[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'AND' : '', $columnName . ' = ' . $joinTableColumnName);
        }
        if ($argsLen === 3) {
            $joinTableColumnName = $args[2];
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
        $columnName = $args[0];
        if ($argsLen === 2) {
            $joinTableColumnName = $args[1];
            $this->ons[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'OR' : '', $columnName . ' = ' . $joinTableColumnName);
        }
        if ($argsLen === 3) {
            $joinTableColumnName = $args[2];
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
        $columnName = $args[0];
        if ($argsLen === 2) {
            if (is_a($args[1], Raw::RAW)) {
                $this->wheres[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'AND' : '', $columnName . ' = ' . $args[1]->getValue());
            } else {
                $this->wheres[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'AND' : '', $columnName . ' = ? ');
                $this->params[] = $args[1];
            }
        }
        if ($argsLen === 3) {
            if (is_a($args[2], Raw::RAW)) {
                $this->wheres[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'AND' : '', $columnName . ' ' . $args[1] . ' ' . $args[1]->getValue());
            } else {
                $this->wheres[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'AND' : '', $columnName . ' ' . $args[1] . ' ? ');
                $this->params[] = $args[2];
            }
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
        $columnName = $args[0];
        if ($argsLen === 2) {
            if (is_a($args[1], Raw::RAW)) {
                $this->wheres[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'OR' : '', $columnName . ' = ' . $args[1].getValue());
            } else {
                $this->wheres[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'OR' : '', $columnName . ' = ? ');
                $this->params[] = $args[1];
            }
        }
        if ($argsLen === 3) {
            if (is_a($args[2], Raw::RAW)) {
                $this->wheres[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'OR' : '', $columnName . ' ' . $args[1] . ' ' . $args[1].getValue());
            } else {
                $this->wheres[] = array(count($this->ons) > 0 || count($this->wheres) > 0 ? 'OR' : '', $columnName . ' ' . $args[1] . ' ? ');
                $this->params[] = $args[2];
            }
        }

        return $this;
    }

    public function getQuery()
    {
        if ($this->isSoftDeleted && !$this->isSoftDeleteLess) {
            if (isset($this->anotherName)) {
                $this->where($this->anotherName . '.' . TableQuery::SoftDeletedColumnName, DB::raw('0'));
            } else {
                $this->where($this->realTableName . '.' . TableQuery::SoftDeletedColumnName, DB::raw('0'));
            }
        }

        $query[] = ($this->isLeftJoin ? 'LEFT JOIN ' : 'JOIN ') . $this->tableName;
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

    public function softDeleteLess()
    {
        $this->isSoftDeleteLess = true;

        return $this;
    }
}

 