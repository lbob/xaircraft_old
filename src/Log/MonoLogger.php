<?php

namespace Xaircraft\Log;
use Xaircraft\Common\IO;
use Xaircraft\Common\Net;
use Xaircraft\Log\Logger;


/**
 * Class MonoLogger
 *
 * @package Xaircraft\Log
 * @author lbob created at 2014/12/29 15:53
 */
class MonoLogger implements Logger {

    private function getLogPath($level)
    {
        $path = \Xaircraft\App::getInstance()->getPath('log') . '/' . $level . '/' . $level . '_' . date("Ymd", time()) . '.log';
        IO::makeDir(dirname($path));
        return $path;
    }

    public function debug($key, $message, array $context = array())
    {
        $context = array_merge($context, array('url' => $_SERVER['REQUEST_URI'], 'ClientIP' => Net::getClientIP()));
        $path = $this->getLogPath('debug');
        $logger = new \Monolog\Logger($key);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($path, \Monolog\Logger::DEBUG));
        return $logger->addDebug($message, $context);
    }

    public function info($key, $message, array $context = array())
    {
        $context = array_merge($context, array('url' => $_SERVER['REQUEST_URI'], 'ClientIP' => Net::getClientIP()));
        $path = $this->getLogPath('info');
        $logger = new \Monolog\Logger($key);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($path, \Monolog\Logger::INFO));
        return $logger->addInfo($message, $context);
    }

    public function notic($key, $message, array $context = array())
    {
        $context = array_merge($context, array('url' => $_SERVER['REQUEST_URI'], 'ClientIP' => Net::getClientIP()));
        $path = $this->getLogPath('notic');
        $logger = new \Monolog\Logger($key);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($path, \Monolog\Logger::NOTICE));
        return $logger->addNotice($message, $context);
    }

    public function warning($key, $message, array $context = array())
    {
        $context = array_merge($context, array('url' => $_SERVER['REQUEST_URI'], 'ClientIP' => Net::getClientIP()));
        $path = $this->getLogPath('warning');
        $logger = new \Monolog\Logger($key);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($path, \Monolog\Logger::WARNING));
        return $logger->addWarning($message, $context);
    }

    public function error($key, $message, array $context = array())
    {
        $context = array_merge($context, array('url' => $_SERVER['REQUEST_URI'], 'ClientIP' => Net::getClientIP()));
        $path = $this->getLogPath('error');
        $logger = new \Monolog\Logger($key);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($path, \Monolog\Logger::ERROR));
        return $logger->addError($message, $context);
    }

    public function critical($key, $message, array $context = array())
    {
        $context = array_merge($context, array('url' => $_SERVER['REQUEST_URI'], 'ClientIP' => Net::getClientIP()));
        $path = $this->getLogPath('critical');
        $logger = new \Monolog\Logger($key);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($path, \Monolog\Logger::CRITICAL));
        return $logger->addCritical($message, $context);
    }

    public function alert($key, $message, array $context = array())
    {
        $context = array_merge($context, array('url' => $_SERVER['REQUEST_URI'], 'ClientIP' => Net::getClientIP()));
        $path = $this->getLogPath('alert');
        $logger = new \Monolog\Logger($key);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($path, \Monolog\Logger::ALERT));
        return $logger->addAlert($message, $context);
    }

    public function emergency($key, $message, array $context = array())
    {
        $context = array_merge($context, array('url' => $_SERVER['REQUEST_URI'], 'ClientIP' => Net::getClientIP()));
        $path = $this->getLogPath('emergency');
        $logger = new \Monolog\Logger($key);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($path, \Monolog\Logger::EMERGENCY));
        return $logger->addEmergency($message, $context);
    }
}

 