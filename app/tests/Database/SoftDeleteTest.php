<?php

/**
 * Class SoftDeleteTest
 *
 * @author skyweo created at 2015/7/8 10:04
 */
class SoftDeleteTest extends DbBaseTest {

    public function testSoftDeleteSelect()
    {
        \Xaircraft\DB::table('post')->insert(array(
            'title' => 'test',
            'deleted_at' => time()
        ))->execute();

        \Xaircraft\DB::table('post')->insert(array(
            'title' => 'test'
        ))->execute();
    }
}

