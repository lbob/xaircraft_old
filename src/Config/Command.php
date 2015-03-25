<?php

namespace Xaircraft\Config;
use Xaircraft\App;
use Xaircraft\Core\Cli\QueueCommand;


/**
 * Class Command
 *
 * @package Xaircraft\Config
 * @author lbob created at 2015/3/25 10:41
 */
class Command {

    public static function load()
    {
        App::registerCommand("queue", QueueCommand::class);
    }
}

 