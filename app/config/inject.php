<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/1/6
 * Time: 16:46
 * @var $this \Xaircraft\App
 */

\Xaircraft\App::bind('Xaircraft\Session\UserSession', function() {
    return new UserSessionImpl();
});

\Xaircraft\App::bind('DatabaseErrorHandler', function() {
    return new DatabaseErrorHandlerImpl();
});

\Xaircraft\App::bind(\Xaircraft\Cache\CacheDriver::class, new \Xaircraft\Cache\RedisCacheDriverImpl());

\Xaircraft\App::bindSingleton('Xaircraft\Session\UserSession', function() {
    return new UserSessionImpl();
});
\Xaircraft\App::bindSingleton('inject_controller');
\Xaircraft\App::bindParam('inject_controller', array('userID' => 4));
\Xaircraft\App::bindParam('Post', array('userName' => 'name test'));