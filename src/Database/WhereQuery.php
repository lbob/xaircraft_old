<?php

namespace Xaircraft\Database;
use Xaircraft\DB;


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
    private $realTableName;
    private $anotherName;
    private $params = array();
    private $isSubQueryMode = false;
    private $selectFields;
    private $subQueryTableName;
    private $isLimit = false;
    private $limitCount = 0;
    private $isSoftDeleted = false;
    private $isSoftDeleteLess = false;

    public function __construct()
    {
        //if (!isset($tableName))
        //    throw new \InvalidArgumentException("Invalid table name");

        //$this->logicTableName = $tableName;
        //$this->prefix = $prefix;

        //if (isset($this->prefix)) $this->tableName = $this->prefix . $tableName;
        //else $this->tableName = $tableName;
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
            $this->params = array_merge($this->params, $ranges);

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
                $this->params = array_merge($this->params, $params);
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
            if (is_a($args[1], Raw::RAW)) {
                $this->wheres[] = array(count($this->wheres) > 0 ? 'OR' : '', $columnName . ' = ' . $args[1]->getValue());
            } else {
                $this->wheres[] = array(count($this->wheres) > 0 ? 'OR' : '', $columnName . ' = ? ');
                $this->params[] = $args[1];
            }
        }
        if ($argsLen === 3) {
            if (is_a($args[2], Raw::RAW)) {
                $this->wheres[] = array(count($this->wheres) > 0 ? 'OR' : '', $columnName . ' ' . $args[1] . ' ' . $args[2]->getValue());
            } else {
                $this->wheres[] = array(count($this->wheres) > 0 ? 'OR' : '', $columnName . ' ' . $args[1] . ' ? ');
                $this->params[] = $args[2];
            }
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

    /**
     * @notify 注意，在子查询中，不支持该函数
     * @return WhereQuery
     */
    public function top()
    {
        $this->isLimit = true;
        $this->limitCount = 1;

        return $this;
    }

    public function from($tableName)
    {
        $this->isSubQueryMode = true;
        if (isset($tableName)) {
            $this->subQueryTableName = $tableName;
        }

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

        if ($this->isSoftDeleted && !$this->isSoftDeleteLess) {
            if (isset($this->anotherName)) {
                $this->where($this->anotherName . '.' . TableQuery::SoftDeletedColumnName, DB::raw('0'));
            } else {
                $this->where($this->realTableName . '.' . TableQuery::SoftDeletedColumnName, DB::raw('0'));
            }
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
                    $fields[] = $item;
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

            if (isset($this->wheres) && count($this->wheres) > 0) {
                $query[] = 'WHERE';
                foreach ($this->wheres as $item) {
                    $query[] = implode(' ', $item);
                }
            }

            if ($this->isLimit && $this->limitCount > 0) {
                $query[] = 'LIMIT 0, ' . $this->limitCount;
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

    public function softDeleteLess()
    {
        $this->isSoftDeleteLess = true;

        return $this;
    }
}

 