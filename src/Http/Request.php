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
        return isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'post';
    }

    public function isXMLHttpRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function isPJAX()
    {
        $pjax = $this->param('_pjax');
        return ((isset($_SERVER['HTTP_X_PJAX']) && strtolower($_SERVER['HTTP_X_PJAX']) === 'true') || isset($pjax));
    }

    public function requestPjaxContainerID()
    {
        if ($this->isPJAX()) {
            $id = $this->param('_pjax');
            return str_replace('#', '', $id);
        }
    }

    public function post($key)
    {
        if (isset($key) && isset($_POST[$key])) {
            return $_POST[$key];
        }
    }

    public function url()
    {
        $namespace = $this->param('namespace');
        return (isset($namespace) ? $namespace . '/' : '') . $this->param('controller') . '/' . $this->param('action');
    }

    public function posts($prefix = null)
    {
        if (isset($prefix) && is_string($prefix)) {
            $posts = array();
            $items = \Xaircraft\Common\Util::fast_array_key_filter($_POST, $prefix . '_');
            foreach ($items as $key => $value) {
                $key = str_replace($prefix . '_', '', $key);
                $posts[$key] = $value;
            }
            return $posts;
        } else {
            return $_POST;
        }
    }
}

 