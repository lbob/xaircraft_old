<?php

/**
 * Class BaseClassTreeTest
 *
 * @author lbob created at 2015/2/9 19:55
 */
class BaseClassTreeTest extends BaseTest {

    protected function setUp()
    {
        parent::setUp();
        \Xaircraft\DB::table('category')->truncate()->execute();
    }

    public function testGetNextClassNo()
    {
        $tree = new \Xaircraft\ERM\BaseClassTree('category', 'classNo');
        \Xaircraft\DB::table('category')->insert(array(
            'classNo' => $tree->getNextClassNo()
        ))->execute();
        $this->assertEquals('0001', \Xaircraft\DB::table('category')->pluck('classNo')->execute());

        \Xaircraft\DB::table('category')->insert(array(
            'classNo' => $tree->getNextClassNo('0001')
        ))->execute();
        $this->assertEquals('00010001', \Xaircraft\DB::table('category')->orderBy('id', 'DESC')->pluck('classNo')->execute());

        \Xaircraft\DB::table('category')->insert(array(
            'classNo' => $tree->getNextClassNo('0001')
        ))->execute();
        $this->assertEquals('00010002', \Xaircraft\DB::table('category')->orderBy('id', 'DESC')->pluck('classNo')->execute());

        \Xaircraft\DB::table('category')->insert(array(
            'classNo' => $tree->getNextClassNo('0001')
        ))->execute();
        $this->assertEquals('00010003', \Xaircraft\DB::table('category')->orderBy('id', 'DESC')->pluck('classNo')->execute());

        \Xaircraft\DB::table('category')->insert(array(
            'classNo' => $tree->getNextClassNo('0001')
        ))->execute();
        $this->assertEquals('00010004', \Xaircraft\DB::table('category')->orderBy('id', 'DESC')->pluck('classNo')->execute());

        \Xaircraft\DB::table('category')->insert(array(
            'classNo' => $tree->getNextClassNo()
        ))->execute();
        $this->assertEquals('0002', \Xaircraft\DB::table('category')->orderBy('id', 'DESC')->pluck('classNo')->execute());

        \Xaircraft\DB::table('category')->insert(array(
            'classNo' => $tree->getNextClassNo()
        ))->execute();
        $this->assertEquals('0003', \Xaircraft\DB::table('category')->orderBy('id', 'DESC')->pluck('classNo')->execute());

        \Xaircraft\DB::table('category')->insert(array(
            'classNo' => $tree->getNextClassNo('0002')
        ))->execute();
        $this->assertEquals('00020001', \Xaircraft\DB::table('category')->orderBy('id', 'DESC')->pluck('classNo')->execute());

        \Xaircraft\DB::table('category')->insert(array(
            'classNo' => $tree->getNextClassNo('0002')
        ))->execute();
        $this->assertEquals('00020002', \Xaircraft\DB::table('category')->orderBy('id', 'DESC')->pluck('classNo')->execute());

        \Xaircraft\DB::table('category')->insert(array(
            'classNo' => $tree->getNextClassNo('0002')
        ))->execute();
        $this->assertEquals('00020003', \Xaircraft\DB::table('category')->orderBy('id', 'DESC')->pluck('classNo')->execute());

        $id0 = \Xaircraft\DB::table('category')->where('classNo', '0002')->pluck('id')->execute();
        $id1 = \Xaircraft\DB::table('category')->where('classNo', '00020001')->pluck('id')->execute();
        $id2 = \Xaircraft\DB::table('category')->where('classNo', '00020002')->pluck('id')->execute();
        $id3 = \Xaircraft\DB::table('category')->where('classNo', '00020003')->pluck('id')->execute();

        $tree->moveTreeNodeAndSave('0002', '0001');

        $this->assertEquals('00010005', \Xaircraft\DB::table('category')->where('id', $id0)->pluck('classNo')->execute());
        $this->assertEquals('000100050001', \Xaircraft\DB::table('category')->where('id', $id1)->pluck('classNo')->execute());
        $this->assertEquals('000100050002', \Xaircraft\DB::table('category')->where('id', $id2)->pluck('classNo')->execute());
        $this->assertEquals('000100050003', \Xaircraft\DB::table('category')->where('id', $id3)->pluck('classNo')->execute());
    }

    protected function tearDown()
    {
        parent::tearDown();
        var_dump(\Xaircraft\DB::getQueryLog());
    }
}

 