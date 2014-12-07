<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2014/12/6
 * Time: 11:08
 * @var $app Xaircraft\App;
 */

use \Xaircraft\App;

$app = App::getInstance();
$app->bindPaths(require __DIR__.'/paths.php');
$app->environment[App::HOST] = 'http://localhost:84';

$app->registerStartHandler(function($app) {
    // Eloquent ORM
    $capsule = new Illuminate\Database\Capsule\Manager();
    $capsule->addConnection(require $app->getPath('config') . '/database.php');
    $capsule->bootEloquent();

    if ($app->environment[App::ENV_MODE] === App::APP_MODE_DEV) {
        // whoops 错误提示
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
        $whoops->register();

        // Ubench 性能工具 Start
        $bench = new Ubench();
        $bench->start();

        $app['bench'] = $bench;
    }
});

$app->registerEndHandler(function($app) {
    if ($app->environment[App::ENV_MODE] === App::APP_MODE_DEV) {
        // Ubench 性能工具 End
        $bench = $app['bench'];
        if (isset($bench)) {
            $bench->end();
            echo '<p style="color:#a0a0a0;text-shadow:1px 1px 0 #FFFFFF;text-align:right;font-size:12px;padding-top:10px;">This page used <strong>' . $bench->getTime() . '</strong>, <strong>' . $bench->getMemoryUsage() . '</strong>.</p>';
        }
    }
});

return $app;