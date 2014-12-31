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
$app->environment[App::ENV_MODE] = App::APP_MODE_DEV;

$app->registerStartHandler(function($app) {
    if ($app->environment[App::ENV_MODE] === App::APP_MODE_DEV) {
        // whoops 错误提示
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
        $whoops->register();

        // Ubench 性能工具 Start
        $app->bench = new Ubench();
        $app->bench->start();
    } else {
        \Xaircraft\DB::disableQueryLog();
    }
});

$app->registerEndHandler(function($app) {
    if ($app->environment[App::ENV_MODE] === App::APP_MODE_DEV && !$app->req->isPJAX()) {
        // Ubench 性能工具 End
        $bench = $app->bench;
        if (isset($bench)) {
            $bench->end();
            echo '<p style="color:#a0a0a0;text-shadow:1px 1px 0 #FFFFFF;text-align:right;font-size:12px;padding-top:10px;">This page used <strong>' . $bench->getTime() . '</strong>, <strong>' . $bench->getMemoryUsage() . '</strong>.</p>';
        }
    }
    \Xaircraft\Log::debug('app_end', 'sql query', \Xaircraft\DB::getQueryLog());
});

$app->registerErrorHandler(function($app, \Exception $ex) {
    \Xaircraft\Log::error('app', $ex->getMessage());
    echo "应用程序错误：" . $ex->getMessage();
});

return $app;