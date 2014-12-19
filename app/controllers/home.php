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

        //$this->layout('admin');
        $this->testHere = 'world__' . $test;

        $db = new \Xaircraft\Database\PdoDatabase();
        $db->connection('mysql:dbname=aec_xph;host=localhost;charset=utf8;collation=utf8_general_ci', 'root', '', null);
        $db->transaction(function(\Xaircraft\Database\Database $db) {
            $result = $db->select('SELECT * FROM aec_post');
            $result2 = $db->update("UPDATE aec_post SET title = 'test' WHERE id = ?", array(1));
            $db->delete("DELETE FROM aec_post WHERE id = ?", array(1));
            foreach ($result as $row) {
                echo $row['title'];
            }
            throw new \Exception("test");
        });

        $log = $db->getQueryLog();
        var_dump($log);
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

