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
        var_dump(DB::table('post')->pluck('create_at')->execute());
        var_dump(DB::getQueryLog());

        var_dump(DB::table('post')->whereIn('id', function(\Xaircraft\Database\WhereQuery $whereQuery) {
            $whereQuery->select('id')->from('post')->where('id', '>', 1);
        })->select()->execute());

        $array1 = array(0, 1, 2, 3);
        $array2 = array(2, 3);
        var_dump(array_intersect($array1, $array2));

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
        $query = DB::query("SHOW COLUMNS FROM x_post");
        foreach ($query as $item) {
            var_dump($item);
        }

        var_dump(DB::query("SHOW COLUMNS FROM x_post"));
        $schema = \Xaircraft\Database\TableSchema::load('x_post');
        var_dump($schema->getColumnInfo('title'));
    }

    public function testarray()
    {
        $array = array(
            '343', '234'
        );
        var_dump(is_array($array));
        var_dump(is_callable($array, true));
    }

    public function test_status()
    {
        return $this->status('', 123, array(
            '343', '234'
        ));
    }

    public function test_insert()
    {
        $id = DB::table('post')->insertGetId(array(
            'title' => 'test'
        ))->execute();

        DB::table('post')->where('id - 1', $id - 1)->update(array(
            'content' => 'test'
        ))->execute();

        var_dump(DB::getQueryLog());
        var_dump(DB::table('post')->orderBy('id', 'DESC')->select()->execute());
    }

    public function test_base_model()
    {
        /**
         * @var Post $post
         */
        $post = \Xaircraft\App::get(Post::class);
        $post->fromArray(array(
            'title' => 'test',
            'view_count' => '1234'
        ), true);
        var_dump($post);
    }

    public function test_array()
    {
        var_dump(time());
        $a = 0;
        $list = array();

        for ($i = 0; $i < 10; $i++) {
            $a = $i;
            $list[] = $a;
        }
        var_dump($list);
    }

    public function test_entity()
    {
        $nowDate = \Carbon\Carbon::now();
        $date = new \Carbon\Carbon('2015-4-11 14:20');
        var_dump($date->diffInMinutes($nowDate));

        $post = DB::entity(DB::table('post')->where('id', 1000)->select());
        var_dump($post->isExist());
        var_dump($post->getData());
        var_dump(DB::getQueryLog());
    }
}

