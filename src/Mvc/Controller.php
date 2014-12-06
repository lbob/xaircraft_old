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
use Xaircraft\Mvc\Action\ObjectResult;
use Xaircraft\Mvc\Action\StatusResult;
use Xaircraft\Mvc\Action\TextResult;
use Xaircraft\Mvc\Action\ViewResult;

abstract class Controller
{
    /**
     * @var $app \XAircraft\Http\Request;
     */
    protected $req;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var $app \Xaircraft\App;
     */
    private $app;

    public function __construct()
    {
        $this->app = App::getInstance();
        $this->req = $this->app->req;
    }

    /**
     * @param null $viewName string
     * @return ViewResult
     */
    public function view($viewName = null)
    {
        $result       = new ViewResult($viewName);
        $result->data = $this->data;
        return $result;
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
     * @param $app \Xaircraft\App
     */
    public static function invoke($controller, $action, $namespace = null)
    {
        if (isset($namespace)) {
            $controller = str_replace('/', '_', $namespace).'_'.$controller;
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
        $controller = new $controller();
        return call_user_func(array($controller, $action)); //返回ActionResult
    }
}
 