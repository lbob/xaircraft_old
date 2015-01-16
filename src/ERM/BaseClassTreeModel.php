<?php

namespace Xaircraft\ERM;


/**
 * Class BaseClassTreeModel
 *
 * @package Xaircraft\ERM
 * @author lbob created at 2015/1/16 10:23
 */
class BaseClassTreeModel {
    /**
     * @var int ID
     */
    public $id;
    /**
     * @var string 名称
     */
    public $title;
    /**
     * @var int 层级
     */
    public $level = 0;
    /**
     * @var BaseClassTreeModel 子树
     */
    public $subTree;

    public function __construct($id, $title)
    {
        $this->id = $id;
        $this->title = $title;
    }
}

 