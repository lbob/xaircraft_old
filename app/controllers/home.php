<?php

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
        \Xaircraft\Session::put('test', \Xaircraft\Session::get('test', 0) + 1);

        $test = \Xaircraft\Session::get('test', 0);
        var_dump($test);

        //$this->layout('admin');
        $this->testHere = 'world';
        return $this->view();
    }

    public function test()
    {
        $test = $_SESSION['test'];
        $this->text($test);
    }

    public function hello()
    {
        $home = new home_controller();
        $home->index()->execute();
        \Xaircraft\Helper\Url::redirect('/');
    }
}

