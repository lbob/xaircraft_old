<?php

namespace Xaircraft\ERM;
use Xaircraft\Container;
use Xaircraft\Database\TableQuery;
use Xaircraft\DB;


/**
 * Class Entity
 *
 * @package Xaircraft\ERM
 * @author lbob created at 2014/12/25 11:07
 */
class Entity {

    private $primaryKey;

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
            if (func_num_args() === 2) {
                $primaryKey = func_get_arg(1);
                if (isset($primaryKey))
                    $this->primaryKey = $primaryKey;
            }
            $this->query = DB::table($this->tableName);
        }
        if ($arg instanceof TableQuery) {
            $this->query = $arg;
            $this->tableName = $this->query->tableName;
            $this->primaryKey = $this->query->primaryKey;
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
                    if (is_string($key))
                        $this->columns[$key] = $value;
                }
            }
        }
    }

    public function save()
    {
        if (isset($this->columns[$this->primaryKey])) {
            $key = $this->columns[$this->primaryKey];
            unset($this->columns[$this->primaryKey]);
            $result = $this->query->update($this->columns)->execute();
            $this->columns[$this->primaryKey] = $key;
        } else {
            $result = $this->query->insertGetId($this->columns)->execute();
            if ($result !== false)
                $this->columns[$this->primaryKey] = $result;
        }

        return $result;
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

 