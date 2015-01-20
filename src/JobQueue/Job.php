<?php

namespace Xaircraft\JobQueue;
use Carbon\Carbon;


/**
 * Class Job
 *
 * @package Xaircraft\Storage
 * @author lbob created at 2015/1/20 10:21
 */
class Job {

    const TIME_JOB = 'time_job';
    const ONCE_JOB = 'once_job';

    /**
     * @var string 作业类型
     */
    private $type;
    /**
     * @var Carbon 作业执行时间
     */
    private $date;
    /**
     * @var string 作业执行者名称
     */
    private $handler;
    private $params = array();
    private $level;

    private function __construct($type, $handler, array $params, Carbon $date = null, $level = null)
    {
        $this->type = $type;
        $this->handler = $handler;
        $this->params = $params;
        if (isset($date)) {
            $this->type = self::TIME_JOB;
            $this->date = $date;
        }
        if (isset($level)) {
            $this->level = $level;
        }
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getParams()
    {
        return $this->params;
    }

    public static function createJob($handler, array $params, $level = null)
    {
        $type = isset($date) ? self::TIME_JOB : self::ONCE_JOB;
        return new Job($type, $handler, $params, null, $level);
    }

    public static function createTimeJob($handler, array $params, Carbon $date = null)
    {
        $type = isset($date) ? self::TIME_JOB : self::ONCE_JOB;
        return new Job($type, $handler, $params, $date, null);
    }
}

 