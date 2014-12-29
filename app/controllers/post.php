<?php

use Xaircraft\DB;
use Xaircraft\Log;

/**
 * Class post_controller
 *
 * @author lbob created at 2014/12/29 9:25
 */
class post_controller extends \Xaircraft\Mvc\Controller {

    public function index()
    {
        $query = DB::table('post')->where('id', $this->req->param('id'))->first();
        $post = DB::entity($query);
        if ($post->isExist()) {
            $this->title = $post->title;
            return $this->view();
        } else {
            return $this->view('post.notfound');
        }
    }

    public function edit()
    {
        Log::info('post/edit', 'test edit log.');
        throw new \InvalidArgumentException("test exception.");
        $query = DB::table('post')->where('id', $this->req->param('id'))->first();
        $post = DB::entity($query);
        $this->post = $post;
        if ($this->req->isPost()) {
            if ($post->save($this->req->posts('post'))) {
                Log::debug('post/edit', 'test edit log.', array('query' => DB::getQueryLog()));
                \Xaircraft\Helper\Url::redirect('/post/edit/', array('id' => $post->id));
            }
        }
        return $this->view();
    }
}

 