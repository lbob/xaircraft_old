<?php

namespace Xaircraft\Http;


/**
 * Class Request
 *
 * @package Xaircraft
 * @author lbob created at 2014/12/6 11:57
 */
class Request {

    private $params = array();

    private static $instance;

    private function __construct($params)
    {
        if (isset($params) && !empty($params)) {
            $this->params = $params;
        }
    }

    public static function getInstance($params)
    {
        if (!isset(self::$instance))
            self::$instance = new Request($params);

        return self::$instance;
    }

    public function param($key)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }
    }

    public function params()
    {
        return $this->params;
    }

    public function isPost()
    {
        return isset($_POST['submit']);
    }

    public function post($key)
    {
        if (isset($key) && isset($_POST[$key])) {
            return $_POST[$key];
        }
    }
}

 