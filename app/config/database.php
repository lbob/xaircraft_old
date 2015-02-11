<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2014/11/14
 * Time: 11:49
 */

return array(
    'default' => array(
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'xaircraft',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'    => 'x_'
    ),
    'farm' => array(
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'xair_farm',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_general_ci',
    )
);