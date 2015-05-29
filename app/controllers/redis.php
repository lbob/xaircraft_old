<?php

/**
 * Class redis
 *
 * @author lbob created at 2015/4/2 16:13
 */
class redis_controller extends \Xaircraft\Mvc\Controller {

    public function sub()
    {
        \Xaircraft\Storage\Redis::getInstance()->set('test', time());
        $time = \Xaircraft\Storage\Redis::getInstance()->get('test');
        var_dump($time);
    }
}

 