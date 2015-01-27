<?php

/**
 * Class DbBaseTest
 *
 * @author lbob created at 2015/1/14 12:43
 */
class DbBaseTest extends BaseTest {

    protected function setUp()
    {
        parent::setUp();
        \Xaircraft\DB::beginTransaction();
    }

    protected function tearDown()
    {
        \Xaircraft\DB::rollback();
        parent::tearDown();
    }
}

 