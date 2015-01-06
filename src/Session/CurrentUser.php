<?php

namespace Xaircraft\Session;


/**
 * Class CurrentUser
 *
 * @package Xaircraft\Session
 * @author lbob created at 2015/1/6 17:16
 */
class CurrentUser {
    public $id;
    public $username;

    public function __construct($id, $username)
    {
        $this->id = $id;
        $this->username = $username;
    }
}

 