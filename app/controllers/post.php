<?php

use Xaircraft\DB;
use Xaircraft\Log;
use Xaircraft\Session;
use Xaircraft\Helper\Url;

/**
 * Class post_controller
 *
 * @author lbob created at 2014/12/29 9:25
 */
class post_controller extends \Xaircraft\Mvc\Controller {

    public function __construct()
    {
        $this->layout('admin');
    }

    /**
     * 修改数据表名称
     * @param string $from
     * @param string $to
     * @return array
     */
    public function ref_test()
    {
        if ($this->req->param('op') === '__api_test__') {
            $class   = new ReflectionClass(__CLASS__);
            $methods = $class->getMethods();
            foreach ($methods as $item) {
                var_dump($item->getName());
                var_dump($item->getDocComment());
            }
            return $this->view('test.test');
        }
    }

    public function index()
    {
        $query = DB::table('post')->whereExists(function(\Xaircraft\Database\WhereQuery $query) {
            $query->select()->from('post')->where('id', '>', 0);
        })->page($this->req->param('p'), 3)->select(array(
            'id',
            'title',
            'author',
            'keyword',
            'content',
            'update_at',
            'create_at'
        ))->format(array(
            'id' => \Xaircraft\Database\ColumnFormat::Integer,
            'update_at' => \Xaircraft\Database\ColumnFormat::DateTime,
            'create_at' => \Xaircraft\Database\ColumnFormat::DateTime
        ));
        $result = $query->execute();
        $this->posts = $result['data'];
        $this->pageIndex = $this->req->param('p');
        $this->pageCount = $result['pageCount'];
        $this->recordCount = $result['recordCount'];
        $query = DB::table('post')->pluck('title')->remeber(1)->execute();
        var_dump($result);
        var_dump(DB::getQueryLog());


        $query = DB::table('post')->count();
        $result = $query->execute();
        var_dump($result);

        $query = DB::table('post')->pluck('id');
        $result = $query->execute();
        var_dump($result);

        $query = DB::table('post')->select('id')->single()->format(array(
            'id' => \Xaircraft\Database\ColumnFormat::Integer
        ));
        $result = $query->execute();
        var_dump($result);

        $query = DB::table('post')->select()->format(array(
            'id' => \Xaircraft\Database\ColumnFormat::Integer,
            'update_at' => \Xaircraft\Database\ColumnFormat::DateTime,
            'create_at' => \Xaircraft\Database\ColumnFormat::DateTime,
            'view_count' => \Xaircraft\Database\ColumnFormat::Integer
        ));
        $result = $query->execute();
        var_dump($result);

        return $this->view();
    }

    public function edit()
    {
        $query = DB::table('post')->where('id', $this->req->param('id'))->first();
        $post = DB::entity($query);
        $this->post = $post;
        var_dump($_SERVER['REQUEST_METHOD']);
        if ($this->req->isPost()) {
            if ($post->save($this->req->posts('post'))) {
                Url::redirect('/post/edit/', array('id' => $post->id));
            }
        }
        return $this->view();
    }

    public function show()
    {
        $query = DB::table('post')->pluck('title')->execute();
        var_dump($query);
    }

    public function trans()
    {
        //DB::table('post')->where('id', 2)->delete()->execute();
        DB::transaction(function($db) {
            DB::transaction(function($db) {
                $db->table('post')->where('id', 29)->delete()->execute();

                DB::transaction(function($db) {
                    $db->table('post')->where('id', 32)->delete()->execute();
                });
            });

            DB::transaction(function($db) {
                $db->table('post')->where('id', 33)->delete()->execute();
            });
        });
    }

    public function trans2()
    {
        DB::beginTransaction();

        DB::beginTransaction();
        DB::table('post')->where('id', 29)->delete()->execute();
        //DB::rollback();
        DB::commit();
        DB::beginTransaction();
        DB::table('post')->where('id', 32)->delete()->execute();
        //DB::rollback();
        DB::commit();
        DB::beginTransaction();
        DB::table('post')->where('id', 33)->delete()->execute();
        DB::rollback();
        DB::commit();

        //DB::rollback();
        DB::commit();
    }

    public function where()
    {
        $result = DB::table('post')->whereIn('id', function(\Xaircraft\Database\WhereQuery $whereQuery) {
            $whereQuery->select('id')->from('post')->where('id', '>', 0);
        })->select()->execute();
        var_dump(DB::getQueryLog());
    }

    public function testarray()
    {
        $array = array(
            'id',
            'title',
            'count' => 4
        );

        var_dump($array);

        $result = DB::table('post')->whereIn('id', function(\Xaircraft\Database\WhereQuery $whereQuery) {
            $whereQuery->select('id')->from('post')->where('id', '>', 0);
        })->where('id', '>', DB::raw(0))->select(array(
            'id', 'title',
            'count' => function(\Xaircraft\Database\WhereQuery $whereQuery) {
                $whereQuery->select('COUNT(*)')->from('post')->where('id', DB::raw('x_post.id'));
            }
        ))->remeber(5)->execute();
        var_dump($result);
        var_dump(DB::getQueryLog());
    }

    public function test_test()
    {
        var_dump(DB::table('post')->select('title')->single()->remeber(1)->execute());
        var_dump(DB::getQueryLog());
        //return $this->text('success');
    }

    public function test_redis()
    {
        var_dump(\Carbon\Carbon::now()->format('Y-m-d-H:i:s'));

        $array = array(
            'id' => 23,
            'title' => 'sdfsdfsd'
        );
        \Xaircraft\Storage\Redis::getInstance()->set('test', serialize($array));
        $test = unserialize(\Xaircraft\Storage\Redis::getInstance()->get('test'));
        var_dump($test);
        var_dump(php_sapi_name());

        var_dump(php_uname());
        var_dump(PHP_OS);
        \Xaircraft\App::getInstance()->getOS();
        var_dump(\Xaircraft\App::getInstance());
    }

    public function test_redis2()
    {
        //\Xaircraft\Storage\Redis::setex('test', 10, 'hello');
        $test = \Xaircraft\Storage\Redis::getInstance()->get('test');
        var_dump($test);
    }

    public function test_redis_lpush()
    {
        \Xaircraft\Storage\Redis::getInstance()->lpush('queue', array('hello1', 'hello2'));
    }

    public function test_redis_brpop()
    {
        //\Xaircraft\Storage\Redis::getInstance()->subcribe();
        $result = \Xaircraft\Storage\Redis::getInstance()->brpop(array('queue'), 2);
        var_dump($result);
    }

    public function test_yield()
    {
        foreach ($this->xrange(1, 9, 2) as $number) {
            echo "$number";
        }

        $range = $this->xrange(1, 1000000);
        var_dump($range);
        var_dump($range instanceof Iterator);

        $range->rewind();
        $result = $range->current();
        var_dump($result);

        $range->next();
        $result = $range->current();
        var_dump($result);

        $range->next();
        $result = $range->current();
        var_dump($result);

        $logger = $this->logger("D:\log" . '/log');
        $logger->send('Foo');
        $logger->send('Bar');
        var_dump($logger);

        $gen = $this->gen();
        var_dump($gen->current());
        var_dump($gen->send('ret1'));
        var_dump($gen->send('ret2'));
    }

    function xrange($start, $limit, $step = 1)
    {
        for ($i = $start; $i <= $limit; $i += $step) {
            yield $i;
        }
    }

    function logger($fileName)
    {
        $fileHandler = fopen($fileName, 'a');
        while (true) {
            fwrite($fileHandler, yield . "\n");
        }
    }

    function gen()
    {
        $ret = (yield 'yield1');
        var_dump($ret);
        $ret = (yield 'yield2');
        var_dump($ret);
    }

    public function test_update()
    {
        var_dump(DB::table('post')->select('title')->single()->execute());
        DB::table('post')->update(array(
            'title' => DB::raw("CONCAT('0002', SUBSTRING(title, 5))")
        ))->execute();
        var_dump(DB::table('post')->select('title')->single()->execute());
        var_dump(DB::getQueryLog());
    }

    public function test33()
    {
        $treeNodes = $this->getMenus();

        $array = array();

        for ($i = 0; $i < count($treeNodes); $i++) {
            $node = $this->traceTree($treeNodes[$i]);
            if (isset($node))
                $array[] = $node;
        }

        echo(json_encode($array));
    }

    public function traceTree($node)
    {
        if (!isset($node)) {
            return null;
        }

        if (isset($node->accessNo) && !$this->checkAccess($node->accessNo)) {
            return null;
        }

        $result = $node;
        $subArray = array();

        if (isset($node->subMenu) && !empty($node->subMenu)) {
            for ($i = 0; $i < count($node->subMenu); $i++) {
                $temp = $this->traceTree($node->subMenu[$i]);
                if (isset($temp))
                    $subArray[] = $temp;
            }
        }
        $result->subMenu = $subArray;

        return $result;
    }

    private function checkAccess($accessNo)
    {
        if ($accessNo === '123') {
            return true;
        }
        return false;
    }

    private function getMenus()
    {
        return json_decode('
        [
            {
                "title": "我的首页",
                "target": "_self",
                "url": "#/home/index",
                "accessNo": "1234",
                "subMenu": null
            },
            {
                "title": "用户管理",
                "target": "_self",
                "url": "#/account/index",
                "accessNo": "123",
                "subMenu": [
                    {
                        "title": "用户管理",
                        "target": "_self",
                        "url": "",
                        "accessNo": "123",
                        "subMenu": [
                            {
                                "title": "所有用户",
                                "target": "_self",
                                "url": "#/account/index",
                                "accessNo": "1234",
                                "subMenu": null
                            }
                        ]
                    },
                    {
                        "title": "权限管理",
                        "target": "_self",
                        "url": "",
                        "accessNo": "123",
                        "subMenu": [
                            {
                                "title": "权限项列表",
                                "target": "_self",
                                "url": "#/access/index",
                                "accessNo": "123",
                                "subMenu": null
                            }
                        ]
                    },
                    {
                        "title": "用户组",
                        "target": "_self",
                        "url": "",
                        "accessNo": "123",
                        "subMenu": [
                            {
                                "title": "用户组列表",
                                "target": "_self",
                                "url": "#/user/group/index",
                                "accessNo": "123",
                                "subMenu": null
                            }
                        ]
                    }
                ]
            }
        ]
        ');
    }
}

 