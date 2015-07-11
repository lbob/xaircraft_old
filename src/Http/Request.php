<?php

namespace Xaircraft\Http;
use Xaircraft\App;


/**
 * Class Request
 *
 * @package Xaircraft
 * @author lbob created at 2014/12/6 11:57
 */
class Request
{

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
            return $this->getStringWithHtmlFilter($this->params[$key]);
        }
    }

    public function params()
    {
        $params = array();
        foreach ($this->params as $key => $value) {
            $params[$key] = $this->getStringWithHtmlFilter($value);
        }

        return $params;
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
            return $this->getStringWithHtmlFilter($_POST[$key]);
        }
    }

    public function url()
    {
        $namespace = $this->param('namespace');
        return (isset($namespace) ? $namespace . '/' : '') . $this->param('controller') . '/' . $this->param('action');
    }

    public function fullUri()
    {
        $host = App::getInstance()->environment[App::HOST];
        return (isset($host) ? $host : '') . $_SERVER['REQUEST_URI'];
    }

    public function posts($prefix = null)
    {
        if (isset($prefix) && is_string($prefix)) {
            $posts = array();
            $items = \Xaircraft\Common\Util::fast_array_key_filter($_POST, $prefix . '_');
            foreach ($items as $key => $value) {
                $key         = str_replace($prefix . '_', '', $key);
                $posts[$key] = $this->getStringWithHtmlFilter($value);
            }
            return $posts;
        } else {
            $posts = array();
            foreach ($_POST as $key => $value) {
                $posts[$key] = $this->getStringWithHtmlFilter($value);
            }

            return $posts;
        }
    }

    /**
     * 获取上传的文件信息
     * @param null $keyword
     * @return array
     */
    public function files($keyword = null)
    {
        if (isset($_FILES)) {
            $files = array();
            foreach ($_FILES as $formKeyword => $filesMeta) {
                if (isset($key) && !strtolower($keyword) === strtolower($formKeyword)) {
                    continue;
                }
                $isArr = is_array($filesMeta['name']);
                $fileCount = count($filesMeta['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    $fileInfo = new RequestFileInfo();
                    $fileInfo->name     = $isArr ? $filesMeta['name'][$i] : $filesMeta['name'];
                    $fileInfo->type     = $isArr ? $filesMeta['type'][$i] : $filesMeta['type'];
                    $fileInfo->tmp_name = $isArr ? $filesMeta['tmp_name'][$i] : $filesMeta['tmp_name'];
                    $fileInfo->error    = $isArr ? $filesMeta['error'][$i] : $filesMeta['error'];
                    $fileInfo->size     = $isArr ? $filesMeta['size'][$i] : $filesMeta['size'];
                    $files[] = $fileInfo;
                }
            }
            return $files;
        }
    }

    private function getStringWithHtmlFilter($str)
    {
        if (is_null(json_decode($str))) {
            return htmlspecialchars($str);
        } else {
            return $str;
        }
    }
}

 