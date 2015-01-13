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
}

 