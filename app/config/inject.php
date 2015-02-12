<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/1/6
 * Time: 16:46
 * @var $this \Xaircraft\App
 */

\Xaircraft\App::bind('UserSession', function() {
    return new UserSessionImpl();
});

\Xaircraft\App::bind('DatabaseErrorHandler', function() {
    return new DatabaseErrorHandlerImpl();
});

\Xaircraft\App::bind('CacheDriver', new \Xaircraft\Cache\RedisCacheDriverImpl());