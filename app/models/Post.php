<?php

/**
 * Class Post
 *
 * @author lbob created at 2014/12/8 17:36
 */
class Post extends \Xaircraft\Mvc\BaseModel {

    /**
     * @var string
     */
    public $title;

    /**
     * @var integer
     */
    public $view_count;

    protected $table = 'post';

    public function __construct(\Xaircraft\Session\UserSession $session, $userName)
    {
        var_dump('Post.__construct');
    }
}

 