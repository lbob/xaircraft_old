<?php

namespace Xaircraft\Database;
use Xaircraft\App;
use Xaircraft\ERM\Entity;


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

    private $patternSelectStatement = '#select[ a-zA-Z`][ \*\_a-zA-Z`0-9\,\(\)\.\/\-\+]+[ ]from#i';
    private $patternInsertStatement = '#insert[ ]+into#i';
    private $patternDeleteStatement = '#delete[ ]+from#i';
    private $patternUpdateStatement = '#update[ a-zA-Z`][ \*\_a-zA-Z`0-9\[\]\,\.\/\-\+]+[ ]set#i';

    private $errorState = false;
    private $errorCode;
    private $errorInfo = array();
    private $errorBindParams = array();
    /**
     * @var array 存储的查询语句
     */
    private $statements = array();
    /**
     * @var bool 是否记录查询语句
     */
    private $isLog = true;
    private $prefix;
    private $dbName;
    private $isRollback = false;
    private $isFinishRollback = false;
    /**
     * @var int 事务嵌套层级
     */
    private $transactionLevel = 0;

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

    private function log($statement, $params = null)
    {
        if ($this->isLog) {
            $time = explode(' ', microtime());
            $time = (float)$time[1] + (float)$time[0];
            if (isset($params)) {
                foreach ($params as $item) {
                    $index = stripos($statement, '?');
                    $statement = substr($statement, 0, $index) . "'" . $item . "'" . substr($statement, $index + 1, strlen($statement) - $index);
                }
            }
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
                $this->log($query, $params);
                $stmt = $this->getDriverInstance()->prepare($query);
                $stmt->execute($params);
                $this->recordError($stmt, $params);
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                throw new \Exception("Not SELECT Query.");
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
                $this->log($query, $params);
                $stmt = $this->getDriverInstance()->prepare($query);
                $result = $stmt->execute($params);
                $this->recordError($stmt, $params);
                return $result;
            } else {
                throw new \Exception("Not INSERT Query.");
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
                $this->log($query, $params);
                $stmt = $this->getDriverInstance()->prepare($query);
                $result = $stmt->execute($params);
                $this->recordError($stmt, $params);
                return $result;
            } else {
                throw new \Exception("Not DELETE Query.");
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
                $this->log($query, $params);
                $stmt = $this->getDriverInstance()->prepare($query);
                $result = $stmt->execute($params);
                $this->recordError($stmt, $params);
                return $result;
            } else {
                throw new \Exception("Not UPDATE Query.");
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
            $this->log($query, $params);
            return $this->getDriverInstance()->exec($query);
        }
        return $this->errorState;
    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     */
    public function query($query, array $params = null)
    {
        if (is_string($query)) {
            $this->log($query, $params);
            return $this->getDriverInstance()->query($query);
        }
        return $this->errorState;
    }

    /**
     * 执行一个事务过程，在$handler中抛出异常则将自动执行回滚
     * @param callable $handler
     * @return mixed|void
     * @throws \Exception
     */
    public function transaction(callable $handler)
    {
        try {
            $this->beginTransaction();
            $result = call_user_func($handler, $this);
            $this->commit();
            return $result;
        } catch (\Exception $ex) {
            $this->rollBack();
            throw $ex;
        }
    }

    /**
     * 手动开始一个事务过程
     * @return mixed
     */
    public function beginTransaction()
    {
        ++$this->transactionLevel;
        if ($this->transactionLevel == 1) {
            $this->isFinishRollback = false;
            $this->getDriverInstance()->beginTransaction();
        }
    }

    /**
     * 手动回滚一个事务过程
     * @return mixed
     */
    public function rollback()
    {
        $this->isRollback = true;
        if ($this->transactionLevel == 1) {
            //$this->transactionLevel = 0;
            if (!$this->isFinishRollback) {
                $this->getDriverInstance()->rollBack();
                $this->isFinishRollback = true;
                $this->transactionLevel = 0;
            }
        } else {
            //--$this->transactionLevel;
        }
    }

    /**
     * 手动提交事务查询
     * @return mixed
     */
    public function commit()
    {
        if ($this->transactionLevel == 1) {
            if ($this->isRollback) {
                $this->rollBack();
                $this->isRollback = false;
            } else {
                $this->getDriverInstance()->commit();
                $this->isFinishRollback = false;
                $this->transactionLevel = 0;
            }
        }
        --$this->transactionLevel;
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
     * @param $database
     * @param $prefix
     * @return mixed
     */
    public function connection($dsn, $username, $password, $options, $database = null, $prefix = null)
    {
        if (isset($dsn)) {
            $this->dbh = new \PDO($dsn, $username, $password, $options);
            $this->prefix = $prefix;
            $this->dbName = $database;
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
     * @param $database
     * @param $prefix
     * @return mixed
     */
    public function reconnect($dsn, $username, $password, $options, $database = null, $prefix = null)
    {
        $this->connection($dsn, $username, $password, $options, $database, $prefix);
    }

    /**
     * 获得数据库驱动对象
     * @return \PDO 返回数据库驱动对象
     */
    public function getDbDriver()
    {
        return $this->getDriverInstance();
    }

    /**
     * @param null $name
     * @return mixed
     */
    public function lastInsertId($name = null)
    {
        return $this->getDriverInstance()->lastInsertId($name);
    }

    /**
     * 获得数据表查询对象
     * @param String $tableName 数据表名称
     * @return \Xaircraft\Database\TableQuery
     */
    public function table($tableName)
    {
        if (isset($tableName)) {
            return new TableQuery($this, $tableName, $this->prefix);
        }

        return null;
    }

    /**
     * @param $query
     * @return \Xaircraft\ERM\Entity
     */
    public function entity($query)
    {
        return new Entity($query);
    }

    /**
     * @param $tempTableName
     * @param callable $handler
     * @return TempTableQuery
     */
    public function temptable($tempTableName, callable $handler)
    {
        if (isset($tempTableName) && isset($handler)) {
            return new TempTableQuery($tempTableName, $handler, $this->prefix);
        }

        return null;
    }

    function __destruct()
    {
        $this->disconnect();
    }

    /**
     * 获得上一次执行产生的错误代码
     * @return string
     */
    public function errorCode()
    {
        return $this->errorCode;
    }

    /**
     * 获得上一次执行产生的错误信息
     * @return array
     */
    public function errorInfo()
    {
        return $this->errorInfo;
    }

    /**
     * @param string $value
     * @return Raw
     */
    public function raw($value)
    {
        return new Raw($value);
    }

    private function recordError(\PDOStatement $stmt, array $params = null)
    {
        $errorCode = $stmt->errorCode();
        if (isset($errorCode) && $errorCode !== '00000') {
            $this->errorCode = $errorCode;
            $this->errorInfo = $stmt->errorInfo();

            /**
             * $errorHandler \Xaircraft\Database\DatabaseErrorHandler
             */
            $errorHandler = App::get(DatabaseErrorHandler::class);
            if (isset($errorHandler)) {
                $errorHandler->onError($this->errorCode, $this->errorInfo, $stmt->queryString, $params);
            }
        }
    }

    /**
     * 创建数据库表构造器
     * @return Table
     */
    public function schema()
    {
        return App::get('Xaircraft\Database\Table', array('dbName' => $this->dbName, 'prefix' => $this->prefix));
    }

    /**
     * 获取数据库配置节点名称
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->dbName;
    }
}

 