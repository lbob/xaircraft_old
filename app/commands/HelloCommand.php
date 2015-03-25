<?php

/**
 * Class HelloCommand
 *
 * @author lbob created at 2015/3/25 10:46
 */
class HelloCommand extends \Xaircraft\Core\Cli\Command {

    public function execute()
    {
        $this->showMessage('hello world');
    }
}

 