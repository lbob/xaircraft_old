<?php

/**
 * Class job_controller
 *
 * @author lbob created at 2015/1/20 16:32
 */
class job_controller extends \Xaircraft\Mvc\Controller {

    public function index()
    {
        var_dump('web start.');
        \Xaircraft\Queue::push('SendEmail', array('id' => 23));
        \Xaircraft\Queue::later('SendEmail@time', array(), \Carbon\Carbon::now()->addMinutes(1));
        var_dump('web end.');
    }

    public function test_time()
    {
        $date = \Carbon\Carbon::now()->addMinutes(5);
        \Xaircraft\Queue::later('SendEmail', array('id' => 23), $date);
        \Xaircraft\Queue::later('SendEmail', array('id' => 23), $date);
        \Xaircraft\Queue::later('SendEmail', array('id' => 23), $date);
        foreach (\Xaircraft\Queue::popTimeAll($date) as $item) {
            var_dump($item);
        }
    }
}

 