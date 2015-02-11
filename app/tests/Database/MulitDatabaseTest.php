<?php

/**
 * Class MulitDatabaseTest
 *
 * @author lbob created at 2015/2/11 17:24
 */
class MulitDatabaseTest extends DbBaseTest {

    public function testChangeDatabase()
    {
        $this->assertTrue(\Xaircraft\DB::schema()->hasTable('post')->execute());
        var_dump(\Xaircraft\DB::getQueryLog());
        $this->assertTrue(\Xaircraft\DB::database('farm')->schema()->hasTable('user')->execute());
        var_dump(\Xaircraft\DB::getQueryLog());
    }
}

 