<?php

namespace Xaircraft;
use Xaircraft\Database\PdoDatabase;


/**
 * Class DB
 *
 * @package Xaircraft
 * @author lbob created at 2014/12/25 10:16
 */
class DB {

    /**
     * @var DB
     */
    private static $instance;

    /**
     * @var \Xaircraft\Database\Database
     */
    protected $provider;

    private function __construct(\Xaircraft\Database\Database $provider)
    {
        $this->provider = $provider;

        $config = require App::getInstance()->getPath('config') . '/database.php';

        if (!isset($config) || !is_array($config) || empty($config))
            throw new \InvalidArgumentException("Database config undefined.");
        if (!array_key_exists('driver', $config) || !isset($config['driver']))
            throw new \InvalidArgumentException("Database config must include driver.");
        else
            $dsn[] = $config['driver'] . ':';
        if (!array_key_exists('database', $config) || !isset($config['database']))
            throw new \InvalidArgumentException("Database config must include database name.");
        else
            $dsn[] = 'dbname=' . $config['database'] . ';';
        if (!array_key_exists('host', $config) || !isset($config['host']))
            throw new \InvalidArgumentException("Database config must include host.");
        else
            $dsn[] = 'host=' . $config['host'] . ';';
        if (array_key_exists('charset', $config) && isset($config['charset']))
            $dsn[] = 'charset=' . $config['charset'] . ';';
        if (array_key_exists('collation', $config) && isset($config['collation']))
            $dsn[] = 'collation=' . $config['collation'] . ';';
        $dsn = implode('', $dsn);
        if (!array_key_exists('username', $config) || !isset($config['username']))
            throw new \InvalidArgumentException("Database config must include username.");
        else
            $username = $config['username'];
        if (!array_key_exists('password', $config) || !isset($config['password']))
            throw new \InvalidArgumentException("Database config must include password.");
        else
            $password = $config['password'];
        $prefix = null;
        if (array_key_exists('prefix', $config) && isset($config['prefix']))
            $prefix = $config['prefix'];

        $this->provider->connection($dsn, $username, $password, null, $prefix);
    }

    private static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = self::create(App::getInstance()->environment[App::ENV_DATABASE_PROVIDER]);
        return self::$instance;
    }

    private static function create($provider)
    {
        switch (strtolower($provider)) {
            case 'pdo':
                return new DB(new PdoDatabase());
            default:
                return new DB(new PdoDatabase());
        }
    }

    /**
     * 执行 Select 查询
     * @param $query String 查询语句
     * @return array 返回查询结果的数组
     */
    public static function select($query, array $params = null)
    {
        return self::getInstance()->provider->select($query, $params);
    }

    /**
     * 执行 Insert 查询
     * @param String $query 查询语句
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function insert($query, array $params = null)
    {
        return self::getInstance()->provider->insert($query, $params);
    }

    /**
     * 执行 Delete 查询
     * @param String $query 查询语句
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function delete($query, array $params = null)
    {
        return self::getInstance()->provider->delete($query, $params);
    }

    /**
     * 执行 Update 查询
     * @param String $query 查询语句
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function update($query, array $params = null)
    {
        return self::getInstance()->provider->update($query, $params);
    }

    /**
     * 执行非CRUD操作
     * @param String $query 查询语句
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public static function statement($query, array $params = null)
    {
        return self::getInstance()->provider->statement($query, $params);
    }

    /**
     * @param $query
     * @param array $params
     * @return mixed
     */
    public static function query($query, array $params = null)
    {
        return self::getInstance()->provider->query($query, $params);
    }

    /**
     * 执行一个事务过程，在$handler中抛出异常则将自动执行回滚
     * @param callable $handler
     * @return mixed
     */
    public static function transaction(callable $handler)
    {
        self::getInstance()->provider->transaction($handler);
    }

    /**
     * 手动开始一个事务过程
     * @return mixed
     */
    public static function beginTransaction()
    {
        self::getInstance()->provider->beginTransaction();
    }

    /**
     * 手动回滚一个事务过程
     * @return mixed
     */
    public static function rollback()
    {
        self::getInstance()->provider->rollback();
    }

    /**
     * 手动提交事务查询
     * @return mixed
     */
    public static function commit()
    {
        self::getInstance()->provider->commit();
    }

    /**
     * 禁用查询记录功能
     * @return mixed
     */
    public static function disableQueryLog()
    {
        self::getInstance()->provider->disableQueryLog();
    }

    /**
     * 获得查询语句
     * @return mixed
     */
    public static function getQueryLog()
    {
        return self::getInstance()->provider->getQueryLog();
    }

    /**
     * 建立一个连接
     * @param $dsn
     * @param $username
     * @param $password
     * @param $options
     * @return mixed
     */
    public static function connection($dsn, $username, $password, $options, $prefix = null)
    {
        self::getInstance()->provider->connection($dsn, $username, $password, $options, $prefix);
    }

    /**
     * 关闭现有连接
     * @return mixed
     */
    public static function disconnect()
    {
        self::getInstance()->provider->disconnect();
    }

    /**
     * 重新建立新的连接
     * @param $dsn
     * @param $username
     * @param $password
     * @param $options
     * @return mixed
     */
    public static function reconnect($dsn, $username, $password, $options, $prefix = null)
    {
        self::getInstance()->provider->reconnect($dsn, $username, $password, $options, $prefix);
    }

    /**
     * 获得数据库驱动对象
     * @return \PDO 返回数据库驱动对象
     */
    public static function getDbDriver()
    {
        return self::getInstance()->provider->getDbDriver();
    }

    /**
     * @param null $name
     * @return mixed
     */
    public static function lastInsertId($name = null)
    {
        return self::getInstance()->provider->lastInsertId($name);
    }

    /**
     * 获得数据表查询对象
     * @param String $tableName 数据表名称
     * @return \Xaircraft\Database\TableQuery
     */
    public static function table($tableName)
    {
        return self::getInstance()->provider->table($tableName);
    }

    /**
     * @param $query
     * @return \Xaircraft\ERM\Entity
     */
    public static function entity($query)
    {
        return self::getInstance()->provider->entity($query);
    }
}

 