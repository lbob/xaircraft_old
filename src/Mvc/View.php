<?php

namespace Xaircraft\Mvc;
use Xaircraft\App;


    /**
 * Class View
 *
 * @package XAircraft\Mvc
 * @author lbob created at 2014/11/20 14:56
 */


class View
{
    const ENV_VIEW_FILE_EXT = 'php';
    const VIEW_BASE_PATH = '/app/views/';

    public $view;
    public $data;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public static function make($viewName = null)
    {
        if (!$viewName) {
            throw new \InvalidArgumentException("视图名称不能为空！");
        } else {
            $viewFilePath = self::getFilePath($viewName);
            if (is_file($viewFilePath) && is_readable($viewFilePath)) {
                return new View($viewFilePath);
            } else {
                throw new \UnexpectedValueException("视图文件不存在：$viewFilePath");
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
        return \Xaircraft\App::getInstance()->getPath('base')
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
            extract($this->data);
            require $this->view;
        }
    }
}

 