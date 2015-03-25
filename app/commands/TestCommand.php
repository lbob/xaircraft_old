<?php

/**
 * Class TestCommand
 *
 * @author lbob created at 2015/3/25 10:57
 */
class TestCommand extends \Xaircraft\Core\Cli\Command {

    public function execute()
    {
        var_dump($this->params);
        $this->showMessage('test');
    }
}

 