<?php

/**
 * Class Transport
 *
 * @author lbob created at 2015/4/7 19:28
 */
interface Transport {

    public function send($data);

    public function registerReceivedHandler(callable $handler);
}

 