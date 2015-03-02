<?php

namespace Xaircraft\Config;
use Xaircraft\App;


/**
 * Class Inject
 *
 * @package Xaircraft\Config
 * @author lbob created at 2015/2/12 11:29
 */
class Inject {

    public static function load()
    {
        App::bind('Xaircraft\Database\Table', 'Xaircraft\Database\TableMySQLImpl');
        App::bind('Xaircraft\Log\Logger', 'Xaircraft\Log\MonoLogger');
        App::bind('Xaircraft\Session\SessionProvider', function() {
            switch (strtolower(App::getInstance()->environment[App::ENV_SESSION_PROVIDER])) {
                case 'file':
                    return new \Xaircraft\Session\FileSessionProvider();
                default:
                    return new \Xaircraft\Session\FileSessionProvider();
            }
        });
    }
}

 