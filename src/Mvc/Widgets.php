<?php

namespace Xaircraft\Mvc;

use Xaircraft\App;
use Xaircraft\Helper\Html;

/**
 * Class Widgets
 *
 * @package Xaircraft\Mvc
 * @author lbob created at 2014/12/9 11:21
 */
class Widgets {
    const ENV_WIDGETS_FILE_EXT = 'phtml';
    const LAYOUT_BASE_PATH = '/views/widgets/';

    public $data;
    /**
     * @var \Xaircraft\Http\Request
     */
    public $req;

    private $widgets;

    public function __construct($widgets)
    {
        $this->widgets = $widgets;
        $this->req = App::getInstance()->req;
    }

    /**
     * @param $widgetsName
     * @return Widgets
     */
    public static function make($widgetsName)
    {
        if (!$widgetsName) {
            throw new \InvalidArgumentException("Invalid widgets name");
        } else {
            $filePath = self::getFilePath($widgetsName);
            if (is_file($filePath) && is_readable($filePath)) {
                return new Widgets($filePath);
            } else {
                throw new \UnexpectedValueException("Can't find widgets file $filePath");
            }
        }
    }

    private static function getFilePath($layoutName)
    {
        $filePath  = str_replace('.', '/', $layoutName);
        $extension = App::getInstance()->environment[App::ENV_VIEW_FILE_EXT];
        if (!isset($extension) || $extension === '') {
            $extension = self::ENV_WIDGETS_FILE_EXT;
        }
        return \Xaircraft\App::getInstance()->getPath('app')
        . self::LAYOUT_BASE_PATH . $filePath . '.'
        . $extension;
    }

    public function render()
    {
        if (isset($this->widgets)) {
            extract($this->data);
            require $this->widgets;
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
}

 