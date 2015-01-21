<?php

/**
 * Class job_controller
 *
 * @author lbob created at 2015/1/20 16:32
 */
class job_controller extends \Xaircraft\Mvc\Controller {

    public function index()
    {
        \Xaircraft\Queue::push('SendEmail@test', array('id' => 23));
        \Xaircraft\Queue::push('SendEmail', array('id' => 23), \Xaircraft\JobQueue\JobQueue::JOB_QUEUE_LEVEL_HIGH);
        \Xaircraft\Queue::push('SendEmail', array('id' => 23), \Xaircraft\JobQueue\JobQueue::JOB_QUEUE_LEVEL_LOW);
        \Xaircraft\Queue::push('SendEmail', array('id' => 23));
        $i = 5;
        foreach (\Xaircraft\Queue::waitPopAll(3) as $item) {
            var_dump($item);
            $i--;
            if ($i <= 0) break;
        }
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

 