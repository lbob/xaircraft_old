<?php

namespace Xaircraft;


/**
 * Class DI
 *
 * @package Xaircraft
 * @author lbob created at 2015/2/12 9:34
 */
class DI {

    /**
     * @var DI
     */
    private static $instance;

    private $instances = array();
    private $instanceParams = array();
    private $singletons = array();

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new DI();
        }
        return self::$instance;
    }

    public function bind($interface, $implement, array $params = null)
    {
        $this->instances[$interface] = $implement;
        if (isset($params)) {
            $this->instanceParams[$interface] = $params;
        }
    }

    public function bindSingleton($interface, $implement = null, array $params = null)
    {
        $this->singletons[$interface] = true;
        if (isset($implement)) {
            $this->bind($interface, $implement, $params);
        }
    }

    public function bindParam($interface, array $params)
    {
        if (array_key_exists($interface, $this->instanceParams)) {
            $params = array_merge($this->instanceParams[$interface], $params);
        }

        $this->instanceParams[$interface] = $params;
    }

    /**
     * @param $name
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function get($name, array $params = null)
    {
        if (array_key_exists($name, $this->instances)) {
            $instance = $this->instances[$name];
            if (is_callable($instance)) {
                return $this->createInstance($name, call_user_func($instance));
            }
            return $instance;
        } else if (class_exists($name)) {
            $class = new \ReflectionClass($name);
            $constructor = $class->getConstructor();
            if (!isset($constructor)) {
                return $this->createInstance($name, $class->newInstance());
            }
            $paramPrototypes = $class->getConstructor()->getParameters();
            if (empty($paramPrototypes)) {
                return $this->createInstance($name, $class->newInstance());
            }
            $injectParams = array();
            foreach ($paramPrototypes as $item) {
                $paramPrototypeClass = $item->getClass();
                if (isset($paramPrototypeClass)) {
                    $injectParams[] = $this->get($paramPrototypeClass->getName());
                } else {
                    if (isset($params) && !empty($params) && array_key_exists($item->name, $params)) {
                        $injectParams[] = $params[$item->name];
                    } else if (array_key_exists($name, $this->instanceParams)) {
                        $innerParams = $this->instanceParams[$name];
                        if (isset($innerParams) && is_array($innerParams) && !empty($innerParams) && array_key_exists($item->name, $innerParams)) {
                            $injectParams[] = $innerParams[$item->name];
                        }
                    } if (!$item->allowsNull()) {
                        throw new \Exception("缺少参数 [$item->name]");
                    }
                }
            }
            return $this->createInstance($name, $class->newInstanceArgs($injectParams));
        }
        return null;
    }

    private function createInstance($name, $instance)
    {
        if (array_key_exists($name, $this->singletons)) {
            $this->instances[$name] = $instance;
        }
        return $instance;
    }
}

 