<?php

use Xaircraft\DB;

/**
 * Class home_controller
 *
 * @author lbob created at 2014/12/6 20:00
 */
class home_controller extends \Xaircraft\Mvc\Controller {

    public function __construct()
    {
        $this->layout('admin');
    }

    public function index()
    {
        $this->testHere = 'sfs';

        $query = DB::table('user')->first();
        var_dump($query);

        return $this->view();
    }
}

