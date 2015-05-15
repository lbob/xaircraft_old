<?php

/**
 * Class PackTest
 *
 * @author lbob created at 2015/5/12 17:25
 */
class PackTest extends BaseTest {

    public function testPack()
    {
        $bin = pack('A6', 'adaaac');
        echo "output: " . $bin . "\n";
        echo "output: 0x" . bin2hex($bin) . "\n";
    }
}

 