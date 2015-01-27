<?php


/**
 * Class BaseTest
 *
 * @author lbob created at 2015/1/13 19:26
 */
class BaseTest extends PHPUnit_Framework_TestCase {

    /**
     * @var \Xaircraft\App;
     */
    protected $app;

    protected function setUp()
    {
        $this->app = \Xaircraft\App::getInstance();
        var_dump('test begin..');
    }

    protected function tearDown()
    {
        var_dump('test end..');
        unset($this->app);
    }
}

 