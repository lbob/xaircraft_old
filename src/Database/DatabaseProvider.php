<?php

namespace Xaircraft\Database;


/**
 * Class DatabaseProvider
 *
 * @package Xaircraft
 * @author lbob created at 2014/12/18 22:31
 */
interface DatabaseProvider {
    public function connect($database, $username, $password);
    public function select($query);
    public function insert($query);
    public function delete($query);
    public function update($query);
    public function statement($query);
    public function transaction(callable $handler);
    public function beginTransaction();
    public function rollback();
    public function commit();
    public function disableQueryLog();
    public function getQueryLog();

    /**
     * @param $tableName
     * @return \Xaircraft\Database\Table
     */
    public function table($tableName);
}

 