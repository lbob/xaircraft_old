<?php

/**
 * Class home_controller
 *
 * @author lbob created at 2014/12/6 20:00
 */
class home_controller extends \Xaircraft\Mvc\Controller {

    public function index()
    {
        if ($this->req->isPost()) {
            $this->data['testHere'] = $this->req->post('test_name');
            if ($this->req->post('test_name') === 'hello') {
                var_dump(\Xaircraft\Helper\Url::link('/'));
            }
        } else {
            $this->data['testHere'] = 'world';
        }
        $post = Post::find(1);
        $post->title;
        //$this->data['posts'] = Post::all();
        var_dump($post instanceof ArrayAccess);
        return $this->view();
    }
}

 