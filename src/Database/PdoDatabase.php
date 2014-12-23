<?php

namespace Xaircraft\Database;
use Whoops\Example\Exception;


/**
 * Class PdoDatabase
 *
 * @package Xaircraft\Database
 * @author lbob created at 2014/12/19 14:31
 */
class PdoDatabase implements Database {

    /**
     * @var \PDO
     */
    private $dbh;

    private $patternSelectStatement = '#select[ a-zA-Z][ \*\_a-zA-Z0-9\,\(\)\.]+[ ]from#i';
    private $patternInsertStatement = '#insert[ ]+into#i';
    private $patternDeleteStatement = '#delete[ ]+from#i';
    private $patternUpdateStatement = '#update[ a-zA-Z][ \*\_a-zA-Z0-9\[\]\,\.]+[ ]set#i';

    private $errorState = false;

    /**
     * @var array 存储的查询语句
     */
    private $statements = array();

    /**
     * @var bool 是否记录查询语句
     */
    private $isLog = true;

    private $prefix;

    /**
     * @return \PDO
     */
    private function getDriverInstance()
    {
        if (isset($this->dbh)) {
            return $this->dbh;
        } else {
            throw new \PDOException("未初始化数据连接对象。");
        }
    }

    private function log($statement)
    {
        if ($this->isLog) {
            $time = explode(' ', microtime());
            $time = $time[1] . $time[0];
            $this->statements[] = '[' . $time . '] ' . $statement;
        }
    }

    /**
     * 执行 Select 查询
     * @param $query String 查询语句
     * @return array 返回查询结果的数组
     */
    public function select($query, array $params = null)
    {
        if (is_string($query)) {
            if (preg_match($this->patternSelectStatement, $query)) {
                $this->log($query);
                $stmt = $this->getDriverInstance()->prepare($query);
                $stmt->execute($params);
                return $stmt->fetchAll();
            }
        }
        return null;
    }

    /**
     * 执行 Insert 查询
     * @param String $query 查询语句
     * @return int 返回查询结果记录数
     */
    public function insert($query, array $params = null)
    {
        if (is_string($query)) {
            if (preg_match($this->patternInsertStatement, $query)) {
                $this->log($query);
                $stmt = $this->getDriverInstance()->prepare($query);
                return $stmt->execute($params);
            }
        }
        return $this->errorState;
    }

    /**
     * 执行 Delete 查询
     * @param String $query 查询语句
     * @return int 返回查询结果记录数
     */
    public function delete($query, array $params = null)
    {
        if (is_string($query)) {
            if (preg_match($this->patternDeleteStatement, $query)) {
                $this->log($query);
                $stmt = $this->getDriverInstance()->prepare($query);
                return $stmt->execute($params);
            }
        }
        return $this->errorState;
    }

    /**
     * 执行 Update 查询
     * @param String $query 查询语句
     * @return int 返回查询结果记录数
     */
    public function update($query, array $params = null)
    {
        if (is_string($query)) {
            if (preg_match($this->patternUpdateStatement, $query)) {
                $this->log($query);
                $stmt = $this->getDriverInstance()->prepare($query);
                return $stmt->execute($params);
            }
        }
        return $this->errorState;
    }

    /**
     * 执行自定义的查询
     * @param String $query 查询语句
     * @return mixed 返回查询结果
     */
    public function statement($query, array $params = null)
    {
        if (is_string($query)) {
            $this->log($query);
            return $this->getDriverInstance()->exec($query);
        }
        return $this->errorState;
    }

    /**
     * 执行一个事务过程，在$handler中抛出异常则将自动执行回滚
     * @param callable $handler
     * @return mixed
     */
    public function transaction(callable $handler)
    {
        $dbh = $this->getDriverInstance();

        $dbh->beginTransaction();
        try {
            call_user_func($handler, $this);
            $dbh->commit();
        } catch (\Exception $ex) {
            $dbh->rollBack();
        }
    }

    /**
     * 手动开始一个事务过程
     * @return mixed
     */
    public function beginTransaction()
    {
        $this->getDriverInstance()->beginTransaction();
    }

    /**
     * 手动回滚一个事务过程
     * @return mixed
     */
    public function rollback()
    {
        $this->getDriverInstance()->rollBack();
    }

    /**
     * 手动提交事务查询
     * @return mixed
     */
    public function commit()
    {
        $this->getDriverInstance()->commit();
    }

    /**
     * 禁用查询记录功能
     * @return mixed
     */
    public function disableQueryLog()
    {
        $this->isLog = false;
    }

    /**
     * 获得查询语句
     * @return mixed
     */
    public function getQueryLog()
    {
        return $this->statements;
    }

    /**
     * 建立一个连接
     * @param $dsn
     * @param $username
     * @param $password
     * @param $options
     * @return mixed
     */
    public function connection($dsn, $username, $password, $options, $prefix = null)
    {
        if (isset($dsn)) {
            $this->dbh = new \PDO($dsn, $username, $password, $options);
            $this->prefix = $prefix;
        }
    }

    /**
     * 关闭现有连接
     * @return mixed
     */
    public function disconnect()
    {
        unset($this->dbh);
    }

    /**
     * 重新建立新的连接
     * @param $dsn
     * @param $username
     * @param $password
     * @param $options
     * @return mixed
     */
    public function reconnect($dsn, $username, $password, $options, $prefix = null)
    {
        $this->connection($dsn, $username, $password, $options, $prefix);
    }

    /**
     * 获得数据库驱动对象
     * @return object 返回数据库驱动对象
     */
    public function getDbDriver()
    {
        return $this->getDriverInstance();
    }

    /**
     * 获得数据表查询对象
     * @param String $tableName 数据表名称
     * @return \Xaircraft\Database\TableQuery
     */
    public function table($tableName, $primaryKey = null)
    {
        if (isset($tableName)) {
            return new TableQuery($this, $tableName, $this->prefix, $primaryKey);
        }
    }
}

 