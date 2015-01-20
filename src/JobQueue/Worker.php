<?php

namespace Xaircraft\JobQueue;


/**
 * Class Worker
 *
 * @package Xaircraft\JobQueue
 * @author lbob created at 2015/1/20 19:21
 */
class Worker
{
    private $defaultJobMethodName = 'fire';
    /*
     * @var \Xaircraft\JobQueue\Job
     */
    private $job;

    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    public function run()
    {
        list($className, $methodName) = $this->parseJobHandler();
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Can't find controller [$className].");
        }
        if (!method_exists($className, $methodName)) {
            throw new \InvalidArgumentException("Can't find action [$methodName] in [$className].");
        }
        $job = new $className;
        return call_user_func(array($className, $methodName), $this->job->getParams());
    }

    private function parseJobHandler()
    {
        $handler = $this->job->getHandler();
        if (!isset($handler) || !is_string($handler)) {
            throw new \InvalidArgumentException("Invalid job handler.");
        }
        $sections = explode('@', $this->job->getHandler());
        if (!isset($sections)) {
            throw new \InvalidArgumentException("Invalid job handler.");
        }
        if (!isset($sections[1])) {
            $sections[] = $this->defaultJobMethodName;
        }
        $className = $sections[0];
        $methodName = $sections[1];
        return array($className, $methodName);
    }
}

 