<?php

namespace Xaircraft\Config;


/**
 * Class Inject
 *
 * @package Xaircraft\Config
 * @author lbob created at 2015/2/12 11:29
 */
class Inject {

    public static function load()
    {
        \Xaircraft\App::bind('Xaircraft\Database\Table', 'Xaircraft\Database\TableMySQLImpl');
    }
}

 