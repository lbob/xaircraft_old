<?php

namespace Xaircraft\Mvc\Action;


/**
 * Class StatusResult
 *
 * @package XAircraft\Mvc\Action
 * @author lbob created at 2014/11/25 15:05
 */
class StatusResult extends ActionResult
{
    /**
     * @var int
     */
    public $statusCode = 0;

    /**
     * @var string
     */
    public $message;

    /**
     * @var object
     */
    public $object;

    /**
     * @param $message string
     * @param $statusCode int
     * @param $object object
     */
    public function __construct($message, $statusCode, $object = null) {
        $this->statusCode = $statusCode;
        $this->message = $message;
        $this->object = $object;
    }

    public function execute()
    {
        $json = array();
        //$json['status'] = '0x'.dechex($this->statusCode); 暂时不采用十六进制表示状态码
//        if (strpos($this->statusCode, '0x') === 0)
//            $json['status'] = $this->statusCode;
//        else
//            $json['status'] = '0x'.dechex($this->statusCode);
        $json['status'] = $this->statusCode;
        $json['message'] = $this->message;

        if (isset($this->data) && !empty($this->data)) {
            $json['data'] = $this->data;
        }

        if (isset($this->object)) {
            if (is_object($this->object)) {
                $json[strtolower(get_class($this->object))] = $this->object;
            } else {
                $json[] = $this->object;
            }
        }

        echo json_encode($json);

        $app = \Xaircraft\App::getInstance();
        unset($app['bench']);
    }
}

 