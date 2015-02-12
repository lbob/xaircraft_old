<?php

use \Xaircraft\Session\UserSession;

/**
 * Class inject_controller
 *
 * @author lbob created at 2015/2/11 19:24
 */
class inject_controller extends \Xaircraft\Mvc\Controller {

    public function __construct(TestModel $model = null, UserSession $session = null, $userID = null)
    {
        var_dump($userID);
    }

    public function index()
    {
        \Xaircraft\App::bindParam('Post', array('userName' => 'name testsss'));

        $instance = \Xaircraft\App::get('inject_controller');
        \Xaircraft\App::get('inject_controller');
        var_dump($instance);
        var_dump(\Xaircraft\DI::getInstance());
    }
}

 