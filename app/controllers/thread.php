<?php

/**
 * Class thread_controller
 *
 * @author lbob created at 2015/2/26 16:38
 */
class thread_controller extends \Xaircraft\Mvc\Controller {

    public function index()
    {
        $thread = new AsyncOperation("World");
        if($thread->start())
            printf("Thread #%lu says: %s\n", $thread->getThreadId(), $thread->join());
    }
}

 