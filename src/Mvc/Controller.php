<?php

namespace Xaircraft\Mvc;


/**
 * Class Controller
 *
 * @package XAircraft\Mvc
 * @author lbob created at 2014/11/20 14:55
 */

use Xaircraft\App;
use Xaircraft\Mvc\Action\JsonResult;
use Xaircraft\Mvc\Action\LayoutResult;
use Xaircraft\Mvc\Action\ObjectResult;
use Xaircraft\Mvc\Action\StatusResult;
use Xaircraft\Mvc\Action\TextResult;
use Xaircraft\Mvc\Action\ViewResult;

abstract class Controller
{
    /**
     * @var $req \XAircraft\Http\Request;
     */
    protected $req;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var \Xaircraft\Mvc\Layout
     */
    private $layoutName;
    /**
     * @var bool 结束action执行并返回结果
     */
    private $isEnded = false;

    public function onPageLoad()
    {

    }

    public function onActionExecuted($action)
    {

    }

    public function end()
    {
        $this->isEnded = true;
    }

    /**
     * @param null $viewName string
     * @return ViewResult
     */
    public function view($viewName = null)
    {
        $viewResult       = new ViewResult($viewName);
        $viewResult->data = $this->data;
        if (!isset($this->layoutName)) {
            return $viewResult;
        } else {
            $layoutResult       = new LayoutResult($this->layoutName, $viewResult);
            $layoutResult->data = $this->data;
            return $layoutResult;
        }
    }

    /**
     * @param null $object object
     * @return JsonResult
     */
    public function json($object = null)
    {
        $result       = new JsonResult($object);
        $result->data = $this->data;
        return $result;
    }

    /**
     * @param $text string
     * @return TextResult
     */
    public function text($text)
    {
        $result = new TextResult($text);
        return $result;
    }

    /**
     * @param $message string
     * @param $statusCode int
     * @param null $object object
     * @return StatusResult
     */
    public function status($message, $statusCode, $object = null)
    {
        $result = new StatusResult($message, $statusCode, $object);
        $result->data = $this->data;
        return $result;
    }

    /**
     * @param null $object object
     * @return \Xaircraft\Mvc\Action\ObjectResult
     */
    public function object($object = null)
    {
        $result = new ObjectResult($object);
        return $result;
    }

    /**
     * @param $controller string
     * @param $action string
     */
    public static function invoke($controller, $action, $namespace = null)
    {
        if (isset($namespace)) {
            $controller = str_replace('/', '_', $namespace) . '_' . $controller;
        }

        $actionResult = self::getActionResult($controller, $action);
        if (isset($actionResult) && $actionResult instanceof Action\ActionResult) {
            return $actionResult->execute();
        }
    }

    private static function getActionResult($controller, $action)
    {
        $controller = $controller . '_controller';

        if (!class_exists($controller)) {
            throw new \InvalidArgumentException("Can't find controller [$controller].");
        }
        if (!method_exists($controller, $action)) {
            throw new \InvalidArgumentException("Can't find action [$action] in [$controller].");
        }
        /**
         * @var Controller $controller
         */
        $controller      = App::get($controller);
        $controller->req = App::getInstance()->req;
        $pageLoadResult = $controller->onPageLoad();
        if (isset($pageLoadResult)) {
            return $pageLoadResult;
        }
        if (!$controller->isEnded) {
            /**
             * @var \Xaircraft\Mvc\Action\ActionResult $actionResult
             */
            $actionResult = call_user_func(array($controller, $action)); //返回ActionResult
            $controller->onActionExecuted($action);
            $actionResult->data = $controller->data;
            return $actionResult;
        }
    }

    protected function layout($layoutName)
    {
        $this->layoutName = $layoutName;
    }

    protected function disableLayout()
    {
        unset($this->layoutName);
    }

    public function __set($key, $value)
    {
        if (isset($key) && is_string($key))
            $this->data[$key] = $value;
    }

    public function __get($key)
    {
        if (isset($key) && array_key_exists($key, $this->data))
            return $this->data[$key];
        else
            throw new \InvalidArgumentException("Can't find [$key] in data.");
    }
}
 