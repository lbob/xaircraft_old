<?php

namespace Xaircraft\Mvc\Action;

use Xaircraft\App;
use Xaircraft\Mvc\View;

/**
 * Class ViewResult
 *
 * @package XAircraft\Mvc\Action
 * @author lbob created at 2014/11/25 11:37
 */
class ViewResult extends ActionResult
{

    const DEFAULT_VIEW = 'index';

    /**
     * @var $view View;
     */
    private $view;

    private $name;

    public function __construct($viewName)
    {
        $this->name = $viewName;
        // 若为空，则默认调用 Controller 的 Action 为名字的 View 文件。
        if (!isset($this->name) || empty($this->name)) {
            $app        = Application::getInstance();
            $namespace  = $app->req->param('namespace');
            $controller = $app->req->param('controller');
            $action     = $app->req->param('action');
            $name       = array();
            if (isset($namespace))
                $name[] = $namespace;
            if (isset($controller))
                $name[] = $controller;
            if (isset($action))
                $name[] = $action;
            var_dump($this->name);
            if (!isset($name) || empty($name))
                $name[] = self::DEFAULT_VIEW;

            $this->name = implode(DIRECTORY_SEPARATOR, $name);
        }
    }

    public function execute()
    {
        if (!isset($this->view))
            $this->view = View::make($this->name);
        $this->view->data = $this->data;
        $this->view->render();
    }
}

 