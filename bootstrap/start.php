<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2014/12/6
 * Time: 11:08
 */

use \Xaircraft\App;

$app = App::getInstance();
$app->bindPaths(require __DIR__.'/paths.php');
$app->environment[App::HOST] = 'http://localhost:83';

return $app;