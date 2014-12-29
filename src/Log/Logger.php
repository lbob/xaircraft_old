<?php

namespace Xaircraft\Log;


/**
 * Class Logger
 *
 * @package Xaircraft\Log
 * @author lbob created at 2014/12/29 15:49
 */
interface Logger {
    public function debug($key, $message, array $context = array());
    public function info($key, $message, array $context = array());
    public function notic($key, $message, array $context = array());
    public function warning($key, $message, array $context = array());
    public function error($key, $message, array $context = array());
    public function critical($key, $message, array $context = array());
    public function alert($key, $message, array $context = array());
    public function emergency($key, $message, array $context = array());
}

 