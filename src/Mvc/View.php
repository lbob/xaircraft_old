<?php

namespace Xaircraft\Mvc;
use Xaircraft\App;
use Xaircraft\Helper\Html;


/**
 * Class View
 *
 * @package Xaircraft\Mvc
 * @author lbob created at 2014/11/20 14:56
 */


class View
{
    const ENV_VIEW_FILE_EXT = 'phtml';
    const VIEW_BASE_PATH = '/views/';

    public $view;
    public $data;
    /**
     * @var \Xaircraft\Http\Request
     */
    public $req;
    /**
     * @var \Xaircraft\Http\Response
     */
    public $response;

    /**
     * @var array
     */
    private $pjaxContainers;

    public function __construct($view)
    {
        $this->view = $view;
        $app = App::getInstance();
        $this->req = $app->req;
        $this->response = $app->response;
    }

    public static function make($viewName = null)
    {
        if (!$viewName) {
            throw new \InvalidArgumentException("Invalid view name.");
        } else {
            $viewFilePath = self::getFilePath($viewName);
            if (is_file($viewFilePath) && is_readable($viewFilePath)) {
                return new View($viewFilePath);
            } else {
                throw new \UnexpectedValueException("Can't find view file [$viewFilePath]");
            }
        }
    }

    public function with($key, $value = null)
    {
        $this->data[$key] = $value;
        return $this;
    }

    private static function getFilePath($viewName)
    {
        $filePath  = str_replace('.', '/', $viewName);
        $extension = App::getInstance()->environment[App::ENV_VIEW_FILE_EXT];
        if (!isset($extension) || $extension === '') {
            $extension = self::ENV_VIEW_FILE_EXT;
        }
        return \Xaircraft\App::getInstance()->getPath('app')
        . self::VIEW_BASE_PATH . $filePath . '.'
        . $extension;
    }

    public function __call($method, $parameters)
    {
        if (starts_with($method, 'with')) {
            return $this->with(snake_case(substr($method, 4)), $parameters[0]);
        }
        throw new \BadMethodCallException("方法 [$method] 不存在！");
    }

    public function render()
    {
        if (isset($this->view)) {
            $this->data['title'] = isset($this->data['title']) ? $this->data['title'] : "Undefined title";
            extract($this->data);
            require $this->view;
        }
    }

    public function renderWidgets($widgetsName)
    {
        /**
         * @var $widgets Widgets
         */
        $widgets = Widgets::make($widgetsName);
        $widgets->data = $this->data;
        $widgets->render();
    }

    public function html()
    {
        return new Html($this);
    }

    public function beginPjax($id)
    {
        if (!isset($this->pjaxContainers)) {
            $this->pjaxContainers = array();
        }
        if (isset($id)) {
            if (!isset($this->pjaxContainers[$id])) {
                $pjaxContainer = new PjaxContainer($this);
                $this->pjaxContainers[$id] = $pjaxContainer;
            } else {
                $pjaxContainer = $this->pjaxContainers[$id];
            }
            $pjaxContainer->begin($id);
        }
    }

    public function endPjax($id)
    {
        if (isset($id)) {
            if (isset($this->pjaxContainers[$id])) {
                $this->pjaxContainers[$id]->end();
            }
        }
    }

    public function registerJs($js)
    {
        echo '<script type="text/javascript">' . $js . '</script>';
    }
}

 