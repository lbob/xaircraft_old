<?php

/**
 * Class job_controller
 *
 * @author lbob created at 2015/1/20 16:32
 */
class job_controller extends \Xaircraft\Mvc\Controller {

    public function index()
    {
        $queue = new \Xaircraft\JobQueue\JobQueueRedisImpl();
        $queue->push('SendEmail', array('id' => 23));
        $queue->push('SendEmail', array('id' => 23), \Xaircraft\JobQueue\JobQueue::JOB_QUEUE_LEVEL_HIGH);
        $queue->push('SendEmail', array('id' => 23), \Xaircraft\JobQueue\JobQueue::JOB_QUEUE_LEVEL_LOW);
        $queue->push('SendEmail', array('id' => 23));
        $i = 4;
        foreach ($queue->waitPopAll() as $item) {
            var_dump($item);
            $i--;
            if ($i <= 0) break;
        }
    }

    public function test_time()
    {
        $date = \Carbon\Carbon::now()->addMinutes(5);
        $queue = new \Xaircraft\JobQueue\JobQueueRedisImpl();
        $queue->later('SendEmail', array('id' => 23), $date);
        $queue->later('SendEmail', array('id' => 23), $date);
        $queue->later('SendEmail', array('id' => 23), $date);
        foreach ($queue->popTimeAll($date) as $item) {
            var_dump($item);
        }
    }
}

 