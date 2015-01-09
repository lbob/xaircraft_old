<?php

namespace Xaircraft\Exception;


/**
 * Class InvalidColumnExecption
 *
 * @package Xaircraft\Exception
 * @author lbob created at 2015/1/9 19:44
 */
class InvalidColumnExecption extends \Exception {

    const INVALID_COLUMN_ERROR_CODE = 401;

    private $params = array();

    public function __construct($message = "", $code = 0, array $params = null, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }
}

 