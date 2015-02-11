<?php

/**
 * Class Post
 *
 * @author lbob created at 2014/12/8 17:36
 */
class Post {
    protected $table = 'post';

    public function __construct(\Xaircraft\Session\UserSession $session, $userName)
    {
        var_dump($userName);
    }
}

 