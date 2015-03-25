<?php

/**
 * Class TestCommand
 *
 * @author lbob created at 2015/3/25 10:57
 */
class TestCommand extends \Xaircraft\Core\Cli\Command {

    public function execute()
    {
        var_dump(\Xaircraft\DB::table('post')->select()->execute());
        var_dump($this->params);
        \Xaircraft\Storage\Redis::getInstance()->set('test', 'test');
        var_dump(\Xaircraft\Storage\Redis::getInstance()->get('test'));
        $this->showMessage('test');
        $date = \Carbon\Carbon::now();
        $this->showMessage($date->toDateTimeString());
        $startDate = $date->startOfDay();
        $this->showMessage($date->toDateTimeString());
        $endDate = $date->endOfDay();
        $this->showMessage($date->toDateTimeString());
        $this->showMessage($date->toDateTimeString());
        $this->showMessage($startDate->toDateTimeString());
        $this->showMessage($endDate->toDateTimeString());
    }
}

 