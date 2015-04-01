<?php

namespace Xaircraft\Mvc;
use Xaircraft\Database\ColumnFormat;


/**
 * Class BaseModel
 *
 * @author lbob created at 2015/1/9 17:24
 */
abstract class BaseModel {

    /**
     * @var \ReflectionProperty[]
     */
    protected $properties;

    private $propertyTypePattern = '#\@var[ ]+([a-zA-Z]+)#i';

    public function fromArray(array $data, $strongType = false)
    {
        if (isset($data)) {
            $feilds = get_object_vars($this);
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $feilds)) {
                    if ($strongType) {
                        $this->{$key} = $this->getPrototypeValue($key, $value);
                    } else {
                        $this->{$key} = $value;
                    }
                }
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

    private function getPrototypeValue($key, $value)
    {
        $reflection = new \ReflectionClass(static::class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            if ($property->getName() === $key) {
                $docComment = $property->getDocComment();
                if (preg_match($this->propertyTypePattern, $docComment, $matches)) {
                    if (isset($matches[1])) {
                        $propertyType = $matches[1];
                        $value        = $this->convert($propertyType, $value);
                        break;
                    }
                }
            }
        }
        return $value;
    }

    private function convert($propertyType, $value)
    {
        return ColumnFormat::getFormatValue($propertyType, $value);
    }
}

