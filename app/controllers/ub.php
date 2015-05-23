<?php

/**
 * Class ub_controller
 *
 * @author lbob created at 2015/3/12 19:21
 */
class ub_controller extends \Xaircraft\Mvc\Controller {

    public function onPageLoad()
    {
        //return $this->text('onPageLoad');
    }

    public function test_url()
    {
        var_dump($this->req->fullUri());
        var_dump($this->req->param('anchor'));
        $url = $this->req->fullUri();
        $url = str_replace("?anchor=", '#', $url);
        var_dump($url);
    }
}

 