<?php

namespace Xaircraft\JobQueue;
use Carbon\Carbon;
use Predis\NotSupportedException;


/**
 * Class JobQueueSyncImpl
 *
 * @package Xaircraft\JobQueue
 * @author lbob created at 2015/1/20 19:33
 */
class JobQueueSyncImpl extends JobQueue
{

    /**
     * 推送一个作业到队列中
     * @param $job
     * @param array $params
     * @param $level
     * @return mixed
     */
    public function push($job, array $params, $level = self::JOB_QUEUE_LEVEL_NORMAL)
    {
        $job    = Job::createJob($job, $params, $level);
        $worker = new Worker($job);
        return $worker->run();
    }

    /**
     * 推送一个延迟执行的作业到队列中
     * @param $job
     * @param array $params
     * @param Carbon $date
     * @return mixed
     */
    public function later($job, array $params, Carbon $date)
    {
        $job    = Job::createTimeJob($job, $params, $date);
        $worker = new Worker($job);
        return $worker->run();
    }

    /**
     * 从队列中取出作业集合（阻塞直到取出作业为止）
     * @param int $timeout
     * @return \Iterator|void
     * @throws NotSupportedException
     */
    public function waitPopAll($timeout = 0)
    {
        throw new NotSupportedException("任务队列为同步模式时不支持该方法。");
    }

    /**
     * @param Carbon $date
     * @return null
     * @throws NotSupportedException
     */
    public function popTimeQueueAndPushToJobQueue(Carbon $date = null)
    {
        throw new NotSupportedException("任务队列为同步模式时不支持该方法。");
    }

    public function getJobQueueStatus()
    {
        throw new NotSupportedException("任务队列为同步模式时不支持该方法。");
    }
}

 