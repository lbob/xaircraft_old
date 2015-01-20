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
class JobQueueRedisImpl extends JobQueue {

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
        $job = Job::createJob($job, $params, $level);
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
     * @return \Iterator
     */
    public function waitPopAll($timeout = 0)
    {
        $keys = $this->getQueueKey();
        while (true) {
            $values = $this->driver->brpop($keys, $timeout);
            yield unserialize($values[1]);
        }
    }

    /**
     * @param Carbon $date
     * @return \Iterator
     */
    public function popTimeAll(Carbon $date = null)
    {
        $key = $this->getLaterQueueKey($date);
        while ($this->driver->llen($key) > 0) {
            $value = unserialize($this->driver->rpop($key));
            yield $value;
        }
    }
}

 