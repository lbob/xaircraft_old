<?php

namespace Xaircraft;
use Carbon\Carbon;
use Xaircraft\JobQueue\JobQueue;
use Xaircraft\JobQueue\JobQueueRedisImpl;
use Xaircraft\JobQueue\JobQueueSyncImpl;


/**
 * Class Queue
 *
 * @package Xaircraft
 * @author lbob created at 2015/1/20 19:44
 */
class Queue {

    const JOB_QUEUE_TYPE_REDIS = 'redis';
    const JOB_QUEUE_TYPE_SYNC  = 'sync';

    /**
     * @var \Xaircraft\JobQueue\JobQueue
     */
    private static $instance;

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = self::make();
        }
        return self::$instance;
    }

    private static function make()
    {
        $config = require App::getInstance()->getPath('config') . '/queue.php';

        if (!isset($config['default'])) {
            throw new \InvalidArgumentException("Queue config missed [default : sync|redis].");
        }

        $type = $config['default'];
        $config = 'default';
        if (isset($config['config'])) {
            $config = $config['config'];
        }

        var_dump($config);

        //WINDOWS系统下，任务队列必须采用同步模式（等有空有需要时再为WIN系统实现异步队列的扩展）
        if (App::getInstance()->getOS() === App::OS_WIN) {
            $type = self::JOB_QUEUE_TYPE_SYNC;
        }

        switch (strtolower($type)) {
            case self::JOB_QUEUE_TYPE_REDIS:
                return new JobQueueRedisImpl($config);
            default:
                return new JobQueueSyncImpl();
        }
    }

    /**
     * 推送一个作业到队列中
     * @param $job
     * @param array $params
     * @param $level
     * @return mixed
     */
    public static function push($job, array $params, $level = JobQueue::JOB_QUEUE_LEVEL_NORMAL)
    {
        return self::getInstance()->push($job, $params, $level);
    }

    /**
     * 推送一个延迟执行的作业到队列中
     * @param $job
     * @param array $params
     * @param Carbon $date
     * @return mixed
     */
    public static function later($job, array $params, Carbon $date)
    {
        return self::getInstance()->later($job, $params, $date);
    }

    /**
     * 从队列中取出作业集合（阻塞直到取出作业为止）
     * @return \Iterator
     */
    public static function waitPopAll()
    {
        return self::getInstance()->waitPopAll();
    }

    /**
     * @param Carbon $date
     * @return \Iterator
     */
    public static function popTimeAll(Carbon $date = null)
    {
        return self::getInstance()->popTimeAll();
    }
}

 