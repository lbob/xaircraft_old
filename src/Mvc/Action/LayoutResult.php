<?php

namespace Xaircraft\Mvc\Action;


/**
 * Class LayoutResult
 *
 * @package Xaircraft\Mvc\Action
 * @author lbob created at 2014/12/9 9:45
 */
class LayoutResult extends ActionResult {

    /**
     * @var ViewResult
     */
    private $viewResult;

    private $layoutName;
    /**
     * @var \Xaircraft\Mvc\Layout
     */
    private $layout;

    public function __construct($layoutName, $viewResult)
    {
        $this->layoutName = $layoutName;
        $this->viewResult = $viewResult;
    }

    public function execute()
    {
        if (!isset($this->layout)) {
            $this->layout = \Xaircraft\Mvc\Layout::make($this->layoutName, $this->viewResult);
        }
        $this->layout->data = $this->data;
        $this->layout->render();
    }
}

 