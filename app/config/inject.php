<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/1/6
 * Time: 16:46
 * @var $this \Xaircraft\App
 */

$this->bind('UserSession', function() {
    return new UserSessionImpl();
});

$this->bind('DatabaseErrorHandler', function() {
    return new DatabaseErrorHandlerImpl();
});