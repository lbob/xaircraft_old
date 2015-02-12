<?php

use \Xaircraft\Session\UserSession;

/**
 * Class inject_controller
 *
 * @author lbob created at 2015/2/11 19:24
 */
class inject_controller extends \Xaircraft\Mvc\Controller {

    private $model;

    public function __construct(TestModel $model = null, UserSession $session = null, $userID = null)
    {
        $this->model = $model;
        var_dump($userID);
    }

    public function index()
    {
        var_dump($this->model);
    }
}

 