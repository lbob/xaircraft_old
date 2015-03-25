<?php
/**
 * Cli模式运行框架
 * 参数：php Cli.php [command] [arg1] [arg2] ...
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/1/21
 * Time: 14:51
 */

$argc = $_SERVER['argc'];
$args = $_SERVER['argv'];

$dir = __DIR__;

if (stripos($dir, '\\') !== false) {
    $dir = str_replace('\\', '/', $dir);
}
$sections = explode('/', $dir);
$path = array_shift($sections);
$autoload = null;
foreach ($sections as $item) {
    $path .= '/' . $item;
    if (is_dir($path)) {
        $cliConfigPath = $path . '/cli_autoload.php';
        if (is_file($cliConfigPath) && is_readable($cliConfigPath)) {
            $autoload = $cliConfigPath;
            break;
        }
    }
}

if (isset($autoload)) {
    require $autoload;
    echo \Xaircraft\App::FRAMEWORK . ' v' . \Xaircraft\App::VERSION . ' CLI Mode.' . \Xaircraft\Core\Cli\Command::newLine();
} else {
    echo "Can't load cli config.";
}

if (isset($args[1])) {
    $command = $args[1];
    $parameters = array();
    if ($argc >= 3) {
        $params = $args;
        unset($params[0]);
        unset($params[1]);
        foreach ($params as $item) {
            $parameters[] = $item;
        }
    }
    $command = \Xaircraft\Core\Cli\CommandFactory::create($command, $parameters);
    if (isset($command)) {
        $command->execute();
    } else {
        echo "What's up?";
    }
}