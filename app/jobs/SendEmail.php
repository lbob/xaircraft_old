<?php

/**
 * Class SendEmail
 *
 * @author lbob created at 2015/1/20 20:19
 */
class SendEmail {

    public function fire($params)
    {
        var_dump('fire!!Send email..');
        for ($i = 0; $i < 100000; $i++) {

        }
        var_dump('fire!!Send email..end');
    }

    public function test($params)
    {
        var_dump('test!!Send email..');
    }

    public function time($params)
    {
        var_dump('time!!Send email..');
    }
}

 