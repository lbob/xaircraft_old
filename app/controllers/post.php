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

    public function index()
    {
        $query = DB::table('post')->whereExists(function(\Xaircraft\Database\WhereQuery $query) {
            $query->select()->from('post')->where('id', '>', 1);
        })->page($this->req->param('p'), 3)->select();
        $result = $query->execute();
        $this->posts = $result['data'];
        $this->pageIndex = $this->req->param('p');
        $this->pageCount = $result['pageCount'];
        $this->recordCount = $result['recordCount'];
        var_dump(DB::getQueryLog());
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
        ))->execute();
        var_dump($result);
        var_dump(DB::getQueryLog());
    }
}

 