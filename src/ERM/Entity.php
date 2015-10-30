<?php

namespace Xaircraft\ERM;
use Xaircraft\Container;
use Xaircraft\Database\TableSchema;
use Xaircraft\Database\TableQuery;
use Xaircraft\DB;


/**
 * Class Entity
 *
 * @package Xaircraft\ERM
 * @author lbob created at 2014/12/25 11:07
 */
class Entity {

    public $logicTableName;
    private $tableName;
    /**
     * @var TableQuery
     */
    private $query;
    private $columns = array();
    private $assigments = array();
    private $shadows = array();
    private $isExist = false;

    public function __construct()
    {
        if (func_num_args() === 0)
            throw new \InvalidArgumentException("Missing init arguments.");

        $arg = func_get_arg(0);
        if (is_string($arg)) {
            $this->tableName = $arg;
            $this->logicTableName = $arg;
            $this->query = DB::table($this->tableName);
        }
        if ($arg instanceof TableQuery) {
            $this->query = $arg;
            $this->tableName = $this->query->tableName;
            $this->logicTableName = $this->query->logicTableName;
            $this->initFromQuery();
        }
    }

    private function initFromQuery()
    {
        if (isset($this->query)) {
            $result = $this->query->execute();
            if (isset($result) && is_array($result) && !empty($result)) {
                $row = $result[0];
                foreach ($row as $key => $value) {
                    if (is_string($key)) {
                        $this->columns[$key] = $this->loadPrototypeFromMeta($key, $value);
                    }
                }
                $this->shadows = $this->columns;
                $this->isExist = true;
            } else {
                $this->isExist = false;
            }
        }
    }

    private function loadPrototypeFromMeta($columnName, $columnValue)
    {
        return $this->query->getTableSchema()->phpTypecast($columnName, $columnValue);
    }

    public function save($posts = null)
    {
        $meta = $this->query->getTableSchema();
        if (isset($meta)) {
            $autoIncrementColumn = $meta->autoIncrementColumn;
            if (isset($posts)) {
                foreach ($posts as $key => $value) {
                    $this->columns[$key] = $value;
                    $this->assigments[$key] = true;
                }
            }
            if (isset($this->columns[$autoIncrementColumn]) && $this->columns[$autoIncrementColumn] > 0) {
                $updateColumns = $this->columns;
                unset($updateColumns[$autoIncrementColumn]);
                foreach ($updateColumns as $key => $value) {
                    if (!isset($this->assigments[$key]) || !$this->assigments[$key])
                        unset($updateColumns[$key]);
                    if (array_key_exists($key, $this->shadows) && $this->shadows[$key] == $value)
                        unset($updateColumns[$key]);
                }
                if (isset($updateColumns) && !empty($updateColumns)) {
                    $meta->valid($updateColumns, true);
                    if (array_search('update_at', $meta->getFields()) !== false) {
                        $updateColumns['update_at'] = time();
                    }
                    $result = $this->query->update($updateColumns)->execute();
                }
                else $result = true;
            } else {
                if (isset($this->columns) && !empty($this->columns)) {
                    $meta->valid($this->columns);
                    if (array_search('create_at', $meta->getFields()) !== false) {
                        $this->columns['create_at'] = time();
                        $this->assigments['create_at'] = true;
                    }
                    if (array_search('update_at', $meta->getFields()) !== false) {
                        $this->columns['update_at'] = time();
                        $this->assigments['update_at'] = true;
                    }
                    $result = $this->query->insertGetId($this->columns)->execute();
                    if ($result !== false && isset($autoIncrementColumn)) {
                        $this->columns[$autoIncrementColumn] = $this->loadPrototypeFromMeta($autoIncrementColumn, $result);
                        $this->query->where($autoIncrementColumn, $result);
                    }
                } else {
                    $result = false;
                }
            }

            return $result;
        }
    }

    public function getData()
    {
        return $this->columns;
    }

    public function isExist()
    {
        return $this->isExist;
    }

    public function getFormColumnName($columnName)
    {
        return $this->logicTableName . '_' . $columnName;
    }

    public function __get($key)
    {
        return $this->columns[$key];
    }

    public function __set($key, $value)
    {
        if (isset($key) && is_string($key)) {
            $this->columns[$key] = $value;
            $this->assigments[$key] = true;
        }
        else
            throw new \InvalidArgumentException("Invalid argument of [$key]");
    }
}

 