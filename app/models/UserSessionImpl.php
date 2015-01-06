<?php

/**
 * Class UserSessionImpl
 *
 * @author lbob created at 2015/1/6 16:10
 */
class UserSessionImpl implements \Xaircraft\Session\UserSession {

    const CURRENT_USER_SESSION_ID = 'currentUser';

    /**
     * @param \Xaircraft\Session\CurrentUser $currentUser
     * @return mixed
     */
    public function setCurrentUser(\Xaircraft\Session\CurrentUser $currentUser = null)
    {
        if (!isset($currentUser)) {
            \Xaircraft\Session::forget(self::CURRENT_USER_SESSION_ID);
        } else {
            \Xaircraft\Session::put(self::CURRENT_USER_SESSION_ID, $currentUser);
        }
    }

    /**
     * @return \Xaircraft\Session\CurrentUser
     */
    public function getCurrentUser()
    {
        return \Xaircraft\Session::get(self::CURRENT_USER_SESSION_ID);
    }
}

 