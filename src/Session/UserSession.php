<?php

namespace Xaircraft\Session;


/**
 * Class UserSession
 *
 * @package Xaircraft\Session
 * @author lbob created at 2015/1/6 15:17
 */
interface UserSession {

    /**
     * @param CurrentUser $currentUser
     * @return mixed
     */
    public function setCurrentUser(CurrentUser $currentUser);

    /**
     * @return CurrentUser
     */
    public function getCurrentUser();
}

class CurrentUser {
    public $id;
    public $username;
}

 