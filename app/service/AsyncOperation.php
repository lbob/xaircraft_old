<?php

/**
 * Class AsyncOperation
 *
 * @author lbob created at 2015/2/26 16:39
 */
class AsyncOperation extends \Thread {

    public function __construct($arg){
        $this->arg = $arg;
    }

    public function run(){
        if($this->arg){
            printf("Hello %s\n", $this->arg);
        }
    }
}

 