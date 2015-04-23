<?php

use \Xaircraft\Session\UserSession;

/**
 * Class inject_controller
 *
 * @author lbob created at 2015/2/11 19:24
 */
class inject_controller extends ub_controller {

    private $model;
    /**
     * @var UserSession
     */
    private $session;

    public function __construct($id = null, TestModel $model = null, UserSession $session = null, $userID = null)
    {
        var_dump($id);
        $this->model = $model;
        var_dump($userID);
        var_dump('inject_controller.__construct');
    }

    public function index()
    {
        var_dump($this->model);
        var_dump($this->session);
        var_dump('inject_controller.index');
    }
}

 