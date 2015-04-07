<?php

/**
 * Class TransportTCPImpl
 *
 * @author lbob created at 2015/4/7 20:06
 */
class TransportTCPImpl implements Transport {

    private $receivedHandler;

    public function __construct()
    {
        var_dump("TransportTCPImpl: __construct");
    }

    public function send($data)
    {
        $this->receive($data);
    }

    public function registerReceivedHandler(callable $handler)
    {
        $this->receivedHandler = $handler;
    }

    private function receive($data)
    {
        if (isset($this->receivedHandler)) {
            call_user_func($this->receivedHandler, $data);
        }
    }
}

 