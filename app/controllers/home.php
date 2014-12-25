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
            $result = $db->select('SELECT title,content FROM aec_post');
            $result2 = $db->update("UPDATE aec_post SET title = 'test' WHERE id = ?", array(1));
            $db->delete("DELETE FROM aec_post WHERE id = ?", array(1));
            foreach ($result as $row) {
                echo $row['title'];
            }
            throw new \Exception("test");
        });

        $log = $db->getQueryLog();
        var_dump($log);
        $params = $this->req->params();
        var_dump($params);
        return $this->view();
    }

    public function test()
    {
        $db = new \Xaircraft\Database\PdoDatabase();
        $db->connection('mysql:dbname=aec_xph;host=localhost;charset=utf8;collation=utf8_general_ci', 'root', '', null, 'aec_');
        $query  = $db->table('post', 'id');

        //select
        $result = $query->where('aec_post.id', '<>', 58)
                        ->join('post_category', function($join) {
                            $join->on('aec_post_category.id', '=', 'aec_post.post_category_id');
                            $join->where('aec_post_category.status', '>', 0);
                        })
                        ->orderBy('aec_post.id', 'DESC')
                        ->page(14, 2)
                        ->select('aec_post.id')->execute();
        var_dump($result);

        //insert
//        $result = $query->insertGetId(array(
//            'title' => '测试插入记录'
//        ))->execute();
//        var_dump($result);

        //update
        $query  = $db->table('post', 'id');
        $result = $query->where('id', 1)->update(array(
            'title' => '测试更新语句'
        ))->execute();

        var_dump($result);
        var_dump($db->getQueryLog());

        $this->text($query);
    }

    public function test2()
    {
        $query = \Xaircraft\DB::table('post', 'id');
        //select
        $result = $query->where('aec_post.id', '<>', 58)
            ->join('post_category', function($join) {
                $join->on('aec_post_category.id', '=', 'aec_post.post_category_id');
                $join->where('aec_post_category.status', '>', 0);
            })
            ->orderBy('aec_post.id', 'DESC')
            ->page(14, 2)
            ->select('aec_post.id')->execute();
        var_dump($result);

        //insert
        $result = $query->insertGetId(array(
            'title' => '测试插入记录'
        ))->execute();
        var_dump($result);

        //update
        $query  = \Xaircraft\DB::table('post', 'id');
        $result = $query->where('id', 1)->update(array(
            'title' => '测试更新语句'
        ))->execute();

        var_dump($result);
        var_dump(\Xaircraft\DB::getQueryLog());

        $this->text($query);
    }

    public function hello()
    {
        $home = new home_controller();
        $home->index()->execute();
        \Xaircraft\Helper\Url::redirect('/');
    }
}

