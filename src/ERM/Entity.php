<?php

namespace Xaircraft\ERM;
use Xaircraft\Container;
use Xaircraft\Database\TableMeta;
use Xaircraft\Database\TableQuery;
use Xaircraft\DB;


/**
 * Class Entity
 *
 * @package Xaircraft\ERM
 * @author lbob created at 2014/12/25 11:07
 */
class Entity extends Container {

    private $tableName;
    /**
     * @var TableQuery
     */
    private $query;

    private $columns = array();

    public function __construct()
    {
        if (func_num_args() === 0)
            throw new \InvalidArgumentException("Missing init arguments.");

        $arg = func_get_arg(0);
        if (is_string($arg)) {
            $this->tableName = $arg;
            $this->query = DB::table($this->tableName);
        }
        if ($arg instanceof TableQuery) {
            $this->query = $arg;
            $this->tableName = $this->query->tableName;
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
            }
        }
    }

    private function loadPrototypeFromMeta($columnName, $columnValue)
    {
        $types = $this->query->getTableMeta()->getTypes();
        if (stripos($types[$columnName], 'int') !== false)
            return intval($columnValue);
        else
            return $columnValue;
    }

    public function save()
    {
        $meta = $this->query->getTableMeta();
        if (isset($meta)) {
            $primaryKey = isset($meta->primaryKey[0]) ? $meta->primaryKey[0] : null;
            if (isset($this->columns[$primaryKey])) {
                $key = $this->columns[$primaryKey];
                unset($this->columns[$primaryKey]);
                $result = $this->query->update($this->columns)->execute();
                $this->columns[$primaryKey] = $key;
            } else {
                $result = $this->query->insertGetId($this->columns)->execute();
                if ($result !== false)
                    $this->columns[$primaryKey] = $this->loadPrototypeFromMeta($primaryKey, $result);
            }

            return $result;
        }
    }

    public function getData()
    {
        return $this->columns;
    }

    public function __get($key)
    {
        return $this->columns[$key];
    }

    public function __set($key, $value)
    {
        if (isset($key) && is_string($key))
            $this->columns[$key] = $value;
        else
            throw new \InvalidArgumentException("Invalid argument of [$key]");
    }
}

 