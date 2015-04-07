<?php

/**
 * Class AirkeyModel
 *
 * @author lbob created at 2015/4/7 20:23
 */
class AirkeyModel {

    public $token;

    public $dev_id;

    public $data;

    public function __toString()
    {
        return $this->dev_id . '/' . $this->token . '/' . json_encode($this->data);
    }
}

 