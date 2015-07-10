<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/1/14
 * Time: 9:29
 */

require __DIR__.'/../../vendor/autoload.php';

$app = \Xaircraft\App::getInstance();
$app->bindPaths(require __DIR__ . '/../../bootstrap/paths.php');
$app->environment[\Xaircraft\App::ENV_MODE] = \Xaircraft\App::APP_MODE_TEST;
$app->registerStartHandler(function($app) {
    //time zone
    ini_set('date.timezone','Asia/Shanghai');
});

$app->run();