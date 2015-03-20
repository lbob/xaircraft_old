<?php

namespace Xaircraft\Exception;
use Xaircraft\App;
use Xaircraft\Log;


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

        //TODO: 不应该默认写入日志
        Log::error('StatusException', $message);
    }

    public function getParams()
    {
        return $this->params;
    }
}

 