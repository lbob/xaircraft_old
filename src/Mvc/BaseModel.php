<?php

namespace Xaircraft\Mvc;


/**
 * Class BaseModel
 *
 * @author lbob created at 2015/1/9 17:24
 */
abstract class BaseModel {

    public function fromArray(array $data)
    {
        if (isset($data)) {
            $feilds = get_object_vars($this);
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $feilds))
                    $this->{$key} = $value;
            }
        }
    }

    public function fromObject($object)
    {
        $this->fromArray(get_object_vars($object));
    }

    public function toArray()
    {
        $data = get_object_vars($this);
        return $this->clear($data);
    }

    protected function clear(array &$data)
    {
        return $data;
    }
}

