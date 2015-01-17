<?php

namespace Xaircraft\Storage;

use Predis\Connection\Aggregate\PredisCluster;
use Xaircraft\App;

/**
 * Class Redis
 *
 * @package Xaircraft\Storage
 * @author lbob created at 2015/1/16 19:05
 */
class Redis {

    private static $host = '127.0.0.1';
    private static $port = 6379;
    /**
     * @var \Predis\Client
     */
    private static $driver;

    private static function getInstance()
    {
        if (!isset(self::$driver)) {
            self::$driver = new \Predis\Client(array('host' => self::$host, 'port' => self::$port));
        }
        return self::$driver;
    }

    private static function init($hostName = null)
    {
        $config = require App::getInstance()->getPath('config') . '/redis.php';

        if (isset($config) && is_array($config) && !empty($config)) {
            if (isset($hostName) && isset($config[$hostName])) {
                $host = $config[$hostName];
                self::$host = $host['host'];
                self::$port = $host['port'];
            }
        }
    }

    public static function connection($hostName)
    {
        self::init($hostName);
    }

    public static function set($key, $value)
    {
        self::getInstance()->set($key, $value);
    }

    public static function get($key)
    {
        return self::getInstance()->get($key);
    }

    public static function command($commandName, $params)
    {
        //TODO: 未实现的方法
    }

    public static function lrange($key, $start, $stop)
    {
        return self::getInstance()->lrange($key, $start, $stop);
    }

    public static function pipeline($pipeHandler)
    {
        //TODO: 未实现的方法
    }

    public static function setex($key, $seconds, $value)
    {
        return self::getInstance()->setex($key, $seconds, $value);
    }

    public static function psetex($key, $milliseconds, $value)
    {
        return self::getInstance()->psetex($key, $milliseconds, $value);
    }

    public static function exists($key)
    {
        return self::getInstance()->exists($key);
    }

    public static function del(array $keys)
    {
        return self::getInstance()->del($keys);
    }

    public static function incr($key)
    {
        self::getInstance()->incr($key);
    }

    public static function decr($key)
    {
        self::getInstance()->decr($key);
    }
}

 