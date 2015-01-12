<?php

namespace Xaircraft\Exception;


/**
 * Class StatusException
 *
 * @package Xaircraft\Exception
 * @author lbob created at 2015/1/9 14:48
 */
class StatusException extends \Exception {

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

 