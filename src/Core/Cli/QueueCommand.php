<?php

namespace Xaircraft\Core\Cli;
use Carbon\Carbon;
use Xaircraft\JobQueue\Monitor;
use Xaircraft\JobQueue\Worker;


/**
 * Class QueueCommand
 *
 * @package Xaircraft\Core\Cli
 * @author lbob created at 2015/1/21 15:29
 */
class QueueCommand extends Command {

    public function execute()
    {
        if (isset($this->params[0])) {
            $command = $this->params[0];
            switch (strtolower($command)) {
                case 'listen':
                    $this->listen();
                    break;
                case 'status':
                    $this->status();
                    break;
                case 'stop':
                    $this->stop();
                    break;
            }
        }
    }

    private function listen()
    {
        Monitor::getInstance()->registerJobHandler(function($job) {
            if (isset($job)) {
                $worker = new Worker($job);
                $worker->run();
                $this->showMessage("Job finished: " . $job->getHandler());
            } else {
                $this->showMessage("No job.");
            }
        });
        Monitor::getInstance()->registerStartHandler(function() {
            $this->showMessage("Job monitor started.");
        });
        Monitor::getInstance()->registerStopHandler(function() {
            $this->showMessage("Job monitor stopped.");
        });
        Monitor::getInstance()->start();
    }

    private function status()
    {
        $status = Monitor::getInstance()->status();
        $this->showMessage($this->newLine() .
            "jobPopCount: " . $status['jobPopCount'] . $this->newLine() .
            "isStopped: " . $status['isStopped'] . $this->newLine() .
            "isStarted: " . $status['isStarted'] . $this->newLine() .
            "jobQueueLength: [high=" . $status['jobQueueLength']['high'] . "][normal=" . $status['jobQueueLength']['normal'] . "][low=" . $status['jobQueueLength']['low'] . "]"
        );
    }

    private function stop()
    {
        Monitor::getInstance()->stop();
    }
}

 