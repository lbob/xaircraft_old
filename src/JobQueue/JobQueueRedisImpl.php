<?php

namespace Xaircraft\JobQueue;
use Carbon\Carbon;
use Xaircraft\Storage\Redis;


/**
 * Class JobQueueRedisImpl
 *
 * @package Xaircraft\Storage
 * @author lbob created at 2015/1/20 14:38
 */
class JobQueueRedisImpl extends JobQueue
{

    /**
     * @var \Xaircraft\Storage\Redis
     */
    private $driver;

    public function __construct($config = null)
    {
        $this->driver = Redis::connection($config);
    }

    /**
     * 推送一个作业到队列中
     * @param $job
     * @param array $params
     * @param $level
     * @return mixed
     */
    public function push($job, array $params, $level = self::JOB_QUEUE_LEVEL_NORMAL)
    {
        $job  = Job::createJob($job, $params, $level);
        $keys = $this->getQueueKey($level);
        $this->driver->lpush($keys[0], serialize($job));
    }

    public function later($job, array $params, Carbon $date)
    {
        $job = Job::createTimeJob($job, $params, $date);
        $this->driver->lpush($this->getLaterQueueKey($date), serialize($job));
    }

    /**
     * 从队列中取出作业集合（阻塞直到取出作业为止）
     * @param int $timeout
     * @return \Iterator|void
     */
    public function waitPopAll($timeout = 0)
    {
        $keys = $this->getQueueKey();
        while (true) {
            $values = $this->driver->brpop($keys, $timeout);
            if (!isset($values)) {
                yield;
            } else {
                yield unserialize($values[1]);
            }
        }
    }

    /**
     * @param Carbon $date
     * @return null
     */
    public function popTimeQueueAndPushToJobQueue(Carbon $date = null)
    {
        $key = $this->getLaterQueueKey($date);
        while ($this->driver->llen($key) > 0) {
            $this->driver->rpoplpush($key, JobQueue::JOB_QUEUE_LEVEL_NORMAL);
        }
    }

    public function getJobQueueStatus()
    {
        return array(
            'high'   => Redis::getInstance()->llen($this->getQueueKey(self::JOB_QUEUE_LEVEL_HIGH)[0]),
            'normal' => Redis::getInstance()->llen($this->getQueueKey(self::JOB_QUEUE_LEVEL_NORMAL)[0]),
            'low'    => Redis::getInstance()->llen($this->getQueueKey(self::JOB_QUEUE_LEVEL_LOW)[0])
        );
    }
}

 