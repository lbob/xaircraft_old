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

        $user = DB::entity('user');
        $user->name = '超级管理员';
        $user->no = 'admin';
        
        var_dump($user->save());
//        DB::table('user')->truncate()->execute();

        return $this->view();
    }
}

