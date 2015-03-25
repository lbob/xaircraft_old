<?php

namespace Xaircraft\Core\Cli;

use Xaircraft\App;
use Xaircraft\Exception\ExceptionHelper;

/**
 * Class CommandFactory
 *
 * @package Xaircraft\Core\Cli
 * @author lbob created at 2015/3/25 10:33
 */
class CommandFactory {

    private $commands = array();

    /**
     * @var Command
     */
    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new CommandFactory();
        }

        return self::$instance;
    }

    /**
     * @param $command
     * @param array $params
     * @return Command
     */
    public static function create($command, array $params = null)
    {
        $factory = self::getInstance();

        $command = strtolower($command);

        if (array_key_exists($command, $factory->commands)) {
            return App::get($factory->commands[$command], array('params' => $params));
        }
    }

    public function register($command, $implement)
    {
        ExceptionHelper::ThrowIfSpaceOrEmpty($command, "缺少注册的命令名称");
        ExceptionHelper::ThrowIfNullOrEmpty($implement, "缺少注册的命令");

        $this->commands[strtolower($command)] = $implement;
    }
}

 