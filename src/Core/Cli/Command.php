<?php

namespace Xaircraft\Core\Cli;
use Carbon\Carbon;


/**
 * Class Command
 *
 * @package Xaircraft\Core\Cli
 * @author lbob created at 2015/1/21 15:27
 */
abstract class Command {

    protected $params = array();

    public function __construct(array $params = null)
    {
        $this->params = $params;
    }

    public abstract function execute();

    public static function newLine()
    {
        return chr(10);
    }

    protected function showMessage($message)
    {
        echo "[" . Carbon::now()->format("Y-m-d H:i:s") . "]" . $message . self::newLine();
    }
}

 