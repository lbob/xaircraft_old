<?php

namespace Xaircraft\Database;


/**
 * Class DatabaseErrorHandler
 *
 * @package Xaircraft\Database
 * @author lbob created at 2015/1/8 20:14
 */
interface DatabaseErrorHandler {

    public function onError($errorCode, $errorInfo, $queryString = null, array $params = null);
}

 