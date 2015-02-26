<?php

namespace Xaircraft\JobQueue;
use Carbon\Carbon;
use Xaircraft\Queue;


/**
 * Class JobQueueMonitor
 *
 * @package Xaircraft\JobQueue
 * @author lbob created at 2015/1/21 10:03
 */
class Monitor
{

    private $isStarted = false;
    private $isStopped = true;
    private $jobHandlers = array();
    private $startHandlers = array();
    private $stopHandlers = array();
    private $jobPopCount = 0;
    private $timeout;

    private static $instance;

    private function __construct($timeout)
    {
        $this->timeout = $timeout;
    }

    public static function getInstance($timeout = 20)
    {
        if (!isset(self::$instance)) {
            self::$instance = new Monitor($timeout);
        }
        return self::$instance;
    }

    public function stop()
    {
        $this->isStopped = true;
    }

    public function start()
    {
        if (!$this->isStarted) {
            $this->onStarted();
            $this->monitor();
        }
    }

    private function monitor()
    {
        foreach (Queue::waitPopAll($this->timeout) as $job) {
            Queue::popTimeQueueAndPushToJobQueue(Carbon::now());
            $this->onJob($job);
            if ($this->isStopped) {
                break;
            }
        }
        $this->onStopped();
    }

    public function status()
    {
        return array(
            'jobPopCount'    => $this->jobPopCount,
            'isStopped'      => $this->isStopped ? "true" : "false",
            'isStarted'      => $this->isStarted ? "true" : "false",
            'jobQueueLength' => Queue::getJobQueueStatus()
        );
    }

    public function registerJobHandler($handler)
    {
        if (isset($handler) && is_callable($handler)) {
            $this->jobHandlers[] = $handler;
        }
    }

    public function registerStartHandler($handler)
    {
        if (isset($handler) && is_callable($handler)) {
            $this->startHandlers[] = $handler;
        }
    }

    public function registerStopHandler($handler)
    {
        if (isset($handler) && is_callable($handler)) {
            $this->stopHandlers[] = $handler;
        }
    }

    private function onJob($job)
    {
        $this->jobPopCount++;

        if (isset($this->jobHandlers) && !empty($this->jobHandlers)) {
            try {
                foreach ($this->jobHandlers as $handler) {
                    call_user_func($handler, $job);
                }
            } catch (\Exception $ex) {
                //TODO: Should catch the exception.
            }
        }
    }

    private function onStarted()
    {
        $this->isStarted = true;
        $this->isStopped = false;

        if (isset($this->startHandlers) && !empty($this->startHandlers)) {
            foreach ($this->startHandlers as $handler) {
                call_user_func($handler);
            }
        }
    }

    private function onStopped()
    {
        $this->isStarted = false;
        $this->isStopped = true;

        if (isset($this->stopHandlers) && !empty($this->stopHandlers)) {
            foreach ($this->stopHandlers as $handler) {
                call_user_func($handler);
            }
        }
    }
}

 