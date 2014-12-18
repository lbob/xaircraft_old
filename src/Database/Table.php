<?php

namespace Xaircraft\Database;


/**
 * Class Table
 *
 * @package Xaircraft\Database
 * @author lbob created at 2014/12/18 22:40
 */
interface Table {
    public function get();
    public function where($conditions);
    public function first();
    public function pluck($columnName);
    public function lists($columnName);
    public function distinct();
    public function select();
    public function addSelect();
    public function skip($count);
    public function take($count);
    public function join($tableName, $conditions);
    public function leftJoin($tableName, $conditions);
    public function count();
    public function max($columnName);
    public function min($columnName);
    public function avg($columnName);
    public function sum($columnName);


}

 