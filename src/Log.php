<?php

namespace Xaircraft;
use Xaircraft\Log\Logger;
use Xaircraft\Log\MonoLogger;


/**
 * Class Logger
 *
 * @package Xaircraft
 * @author lbob created at 2014/12/29 15:30
 */
class Log {

    /**
     * @var \Xaircraft\Log\Logger
     */
    private $logger;

    /**
     * @var Log
     */
    private static $instance;

    private function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return Log
     */
    private static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = App::get('Xaircraft\Log\Logger');
        return self::$instance;
    }

    public static function info($key, $message, array $context = array())
    {
        return self::getInstance()->logger->info($key, $message, $context);
    }

    public static function debug($key, $message, array $context = array())
    {
        return self::getInstance()->logger->debug($key, $message, $context);
    }

    public static function notic($key, $message, array $context = array())
    {
        return self::getInstance()->logger->notic($key, $message, $context);
    }

    public static function warning($key, $message, array $context = array())
    {
        return self::getInstance()->logger->warning($key, $message, $context);
    }

    public static function error($key, $message, array $context = array())
    {
        return self::getInstance()->logger->error($key, $message, $context);
    }

    public static function critical($key, $message, array $context = array())
    {
        return self::getInstance()->logger->critical($key, $message, $context);
    }

    public static function alert($key, $message, array $context = array())
    {
        return self::getInstance()->logger->alert($key, $message, $context);
    }

    public static function emergency($key, $message, array $context = array())
    {
        return self::getInstance()->logger->emergency($key, $message, $context);
    }
}

 