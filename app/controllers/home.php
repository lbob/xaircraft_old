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
        $userSession = \Xaircraft\App::getInstance()->getUserSession();
        $current = new \Xaircraft\Session\CurrentUser(1, 'liub');
        $userSession->setCurrentUser($current);
        var_dump($userSession->getCurrentUser());

        $this->testHere = 'sfs';

        $user = DB::entity('user');
        $user->name = '超级管理员';
        $user->no = 'admin';
        
        var_dump($user->save());
//        DB::table('user')->truncate()->execute();

        return $this->view();
    }

    public function hello()
    {
        $userSession = \Xaircraft\App::getInstance()->getUserSession();
        var_dump($userSession->getCurrentUser());
        $hash = password_hash('123456', PASSWORD_BCRYPT);

        $result = password_verify('123456', $hash);
        var_dump($result);

        return $this->text($hash);
    }

    public function test()
    {
        $result = DB::table('post')->insert(array(
            'title' => 'test'
        ))->execute();
        var_dump($result);
        var_dump(DB::errorCode());
        var_dump(DB::getQueryLog());

        $query = "INSERT INTO x_post (id) VALUES ( ?,?) ?";
        $params = array(1, 2, 3);

        foreach ($params as $item) {
            $index = stripos($query, '?');
            $query = substr($query, 0, $index) . "'" . $item . "'" . substr($query, $index + 1, strlen($query) - $index);
            var_dump($query);
            var_dump($index);
        }

        var_dump(DB::table('post')->select()->execute());
    }

    public function testwhere()
    {
        $result = DB::table('user')->where(function(\Xaircraft\Database\WhereQuery $query) {
            //$query->select()
        });
    }
}

