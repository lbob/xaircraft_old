<?php

namespace Xaircraft\JobQueue;
use Carbon\Carbon;


/**
 * Class JobQueue
 *
 * @package Xaircraft\Storage
 * @author lbob created at 2015/1/20 10:06
 */
abstract class JobQueue {

    const JOB_QUEUE_KEY = 'job_queue';
    const JOB_QUEUE_LEVEL_HIGH = 'job_queue_level_high';
    const JOB_QUEUE_LEVEL_NORMAL = 'job_queue_level_normal';
    const JOB_QUEUE_LEVEL_LOW = 'job_queue_level_low';

    /**
     * 推送一个作业到队列中
     * @param $job
     * @param array $params
     * @param $level
     * @return mixed
     */
    public abstract function push($job, array $params, $level = self::JOB_QUEUE_LEVEL_NORMAL);

    /**
     * 推送一个延迟执行的作业到队列中
     * @param $job
     * @param array $params
     * @param Carbon $date
     * @return mixed
     */
    public abstract function later($job, array $params, Carbon $date);

    /**
     * 从队列中取出作业集合（阻塞直到取出作业为止）
     * @return \Iterator
     */
    public abstract function waitPopAll();

    /**
     * @param Carbon $date
     * @return \Iterator
     */
    public abstract function popTimeAll(Carbon $date = null);

    /**
     * @param null $level
     * @return array
     */
    protected function getQueueKey($level = null)
    {
        if (isset($level)) {
            return array($level);
        } else {
            return array(
                JobQueue::JOB_QUEUE_LEVEL_HIGH,
                JobQueue::JOB_QUEUE_LEVEL_NORMAL,
                JobQueue::JOB_QUEUE_LEVEL_LOW
            );
        }
    }

    protected function getLaterQueueKey(Carbon $date = null)
    {
        if (!isset($date)) {
            $date = Carbon::now();
        }
        return JobQueue::JOB_QUEUE_KEY . '-time-' . $date->format('Y-m-d-H:i');
    }
}

 