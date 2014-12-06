<?php

namespace Xaircraft;


/**
 * Class ClassLoader
 *
 * @package Xaircraft
 * @author lbob created at 2014/12/6 20:17
 */
class ClassLoader extends Container {

    private $paths = array();

    public function __construct()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    public function __destruct()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    public function addPath($path)
    {
        if (!in_array($path, $this->paths) && is_dir($path))
            $this->paths[] = $path;
    }

    private function loadClass($className)
    {
        if (isset($className) && !isset($this[$className])) {
            $this[$className] = true;
            $className = strtolower($className);

            $path = null;
            // Controller
            if (strpos($className, "_controller") > 0) {
                $path = $this->getControllerPath($className);
            }
            if (is_file($path) && is_readable($path)) {
                require_once $path;
            } else {
                $path = $this->scan($className);
                if (isset($path)) {
                    require_once $path;
                } else {
                    throw new \Exception("Can't load class [$className].");
                }
            }
        }
    }

    private function getControllerPath($className)
    {
        $className = substr($className, 0, strpos($className, "_controller"));
        $className = str_replace('_', DIRECTORY_SEPARATOR, $className);
        return App::path("app").'/controllers/'.$className.'.php';
    }

    private function scan($className)
    {
        $className = str_replace('_', DIRECTORY_SEPARATOR, $className);

        foreach ($this->paths as $path) {
            $file = $path.'/'.$className.'.php';
            if (is_file($file) && is_readable($file)) {
                return $file;
            }
        }
        return null;
    }
}

 