<?php

namespace Xaircraft\Mvc;

use Xaircraft\App;
use Xaircraft\Helper\Html;
use Xaircraft\Mvc\Action\ViewResult;

/**
 * Class Layout
 *
 * @package Xaircraft\Mvc
 * @author lbob created at 2014/12/9 9:13
 */
class Layout {

    const ENV_LAYOUT_FILE_EXT = 'phtml';
    const LAYOUT_BASE_PATH = '/views/layout/';

    public $data;
    /**
     * @var \Xaircraft\Http\Request
     */
    public $req;
    /**
     * @var \Xaircraft\Mvc\Action\ViewResult
     */
    private $viewResult;

    private $layout;

    public function __construct($layout, $viewResult)
    {
        $this->layout = $layout;
        $this->viewResult = $viewResult;
        $this->req = App::getInstance()->req;
    }

    public function make($layoutName, $viewResult)
    {
        if (!$layoutName) {
            throw new \InvalidArgumentException("Invalid layout name");
        } else {
            $layoutFilePath = self::getFilePath($layoutName);
            if (is_file($layoutFilePath) && is_readable($layoutFilePath)) {
                return new Layout($layoutFilePath, $viewResult);
            } else {
                throw new \UnexpectedValueException("Can't find layout file $layoutFilePath");
            }
        }
    }

    public function renderBody()
    {
        if (isset($this->viewResult)) {
            $this->viewResult->execute();
        }
    }

    public function renderPage($viewName)
    {
        $viewResult       = new ViewResult($viewName);
        $viewResult->data = $this->data;
        $viewResult->execute();
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

    private static function getFilePath($layoutName)
    {
        $filePath  = str_replace('.', '/', $layoutName);
        $extension = App::getInstance()->environment[App::ENV_VIEW_FILE_EXT];
        if (!isset($extension) || $extension === '') {
            $extension = self::ENV_LAYOUT_FILE_EXT;
        }
        return \Xaircraft\App::getInstance()->getPath('app')
        . self::LAYOUT_BASE_PATH . $filePath . '.'
        . $extension;
    }

    public function render()
    {
        if (isset($this->layout)) {
            $this->data['title'] = isset($this->data['title']) ? $this->data['title'] : "Undefined title";
            extract($this->data);
            require $this->layout;
        }
    }

    public function html()
    {
        return new Html($this);
    }
}

 