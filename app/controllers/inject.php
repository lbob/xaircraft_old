<?php

use \Xaircraft\Session\UserSession;

/**
 * Class inject_controller
 *
 * @author lbob created at 2015/2/11 19:24
 */
class inject_controller extends \Xaircraft\Mvc\Controller {

    public function __construct(TestModel $model = null, UserSession $session = null, $userID = null)
    {
        var_dump($userID);
    }

    public function index()
    {
        $this->instances = array(
            'Xaircraft\Session\UserSession' => function() {
                return new UserSessionImpl();
            }
        );
        $instance = $this->get('inject_controller', array('userID' => 3));
        var_dump($instance);
    }

    private $instances = array();

    /**
     * @param $name
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    private function get($name, array $params = null)
    {
        if (class_exists($name)) {
            $class = new ReflectionClass($name);
            $constructor = $class->getConstructor();
            if (!isset($constructor)) {
                return $class->newInstance();
            }
            $paramPrototypes = $class->getConstructor()->getParameters();
            if (empty($paramPrototypes)) {
                return $class->newInstance();
            }
            $injectParams = array();
            foreach ($paramPrototypes as $item) {
                $paramPrototypeClass = $item->getClass();
                if (isset($paramPrototypeClass)) {
                    $injectParams[] = $this->get($paramPrototypeClass->getName(), $params);
                } else {
                    if (isset($params) && !empty($params) && array_key_exists($item->name, $params)) {
                        $injectParams[] = $params[$item->name];
                    } else if (!$item->allowsNull()) {
                        throw new \Exception("缺少参数 [$item->name]");
                    }
                }
            }
            return $class->newInstanceArgs($injectParams);
        } else if (array_key_exists($name, $this->instances)) {
            $instance = $this->instances[$name];
            if (is_callable($instance)) {
                return call_user_func($instance);
            }
            return $instance;
        } else {
            throw new \Exception("找不到类的定义 [$name]");
        }
    }
}

 