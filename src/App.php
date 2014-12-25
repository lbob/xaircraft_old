<?php

namespace Xaircraft;


/**
 * Class App
 *
 * @package Xaircraft
 * @author lbob created at 2014/12/6 11:08
 * @website https://bitbucket.org/skyweo/xaircraft
 */
class App extends Container {

    const ENV_MODE = 'mode';
    const ENV_VIEW_FILE_EXT = 'view_file_ext';
    const ENV_DEFAULT_TOKEN = 'default_token';
    const ENV_SESSION_PROVIDER = 'session_provider';
    const ENV_DATABASE_PROVIDER = 'database_provider';
    const APP_MODE_DEV = 'dev';
    const APP_MODE_PUB = 'pub';
    const HOST = 'host';

    /**
     * @var $req Http\Request;
     */
    public $req;
    public $environment = array();

    private $startHandlers = array();
    private $endHandlers = array();
    private $isStarted = false;
    private $isEnded = false;
    private $paths = array();
    /**
     * @var \Xaircraft\ClassLoader
     */
    private $classLoader;
    /**
     * @var \Nebula\Router;
     */
    private $router;
    /**
     * @var App
     */
    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new App();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->environment = array(
            self::ENV_MODE          => self::APP_MODE_DEV,
            self::ENV_DEFAULT_TOKEN => array(
                'controller' => 'home',
                'action'     => 'index'
            ),
            self::ENV_VIEW_FILE_EXT => '',
            self::ENV_SESSION_PROVIDER => 'file',
            self::ENV_DATABASE_PROVIDER=> 'pdo',
            self::HOST              => ''
        );
    }

    public function bindPaths($paths)
    {
        if (isset($paths) && !empty($paths) && is_array($paths)) {
            $this->paths = $paths;
        } else {
            throw new \InvalidArgumentException('Invalid install paths.');
        }
    }

    public function run()
    {
        $this->onStart();
        $this->autoload();
        $this->routing();
        $this->onEnd();
    }

    public function getPath($key)
    {
        if (array_key_exists($key, $this->paths)) {
            return $this->paths[$key];
        }
    }

    public static function path($key)
    {
        $app = App::getInstance();
        return $app->getPath($key);
    }

    private function autoload()
    {
        $this->classLoader = new ClassLoader();
        $this->classLoader->addPath(\Xaircraft\App::path("app").'/models');
    }

    private function routing()
    {
        $this->router = \Nebula\Router::getInstance($this->paths['routes'], $this->paths['filter']);
        $this->router->baseMappings['default']['default'] = $this->environment[self::ENV_DEFAULT_TOKEN];

        $this->router->registerMatchedHandler(function ($params) {
            $this->req = Http\Request::getInstance($params);
        });

        $this->router->registerDefaultMatchedHandler(function ($params) {
            $namespace = null;
            if (array_key_exists('namespace', $params))
                $namespace = $params['namespace'];
            if (array_key_exists('controller', $params))
                $controller = $params['controller'];
            if (array_key_exists('action', $params))
                $action = $params['action'];
            if (!isset($controller))
                $controller = $this->environment[self::ENV_DEFAULT_TOKEN]['controller'];
            if (!isset($action))
                $action = $this->environment[self::ENV_DEFAULT_TOKEN]['action'];
            \Xaircraft\Mvc\Controller::invoke($controller, $action, $namespace);
        });

        $this->router->missing(function() {
            throw new \Exception("URL Routing missing.");
        });

        $this->router->routing();
    }

    public function registerStartHandler($handler)
    {
        if (isset($handler) && is_callable($handler)) {
            $this->startHandlers[] = $handler;
        }
    }

    public function registerEndHandler($handler)
    {
        if (isset($handler) && is_callable($handler)) {
            $this->endHandlers[] = $handler;
        }
    }

    private function onStart()
    {
        if ($this->isStarted) {
            return;
        }
        $this->isStarted = true;
        if (isset($this->startHandlers)) {
            foreach ($this->startHandlers as $handler) {
                $handler($this);
            }
        }
    }

    private function onEnd()
    {
        if ($this->isEnded || !$this->isStarted) {
            return;
        }
        $this->isEnded = true;
        if (isset($this->endHandlers)) {
            foreach ($this->endHandlers as $handler) {
                $handler($this);
            }
        }
    }

    public function __get($key)
    {
        return $this[$key];
    }

    public function __set($key, $value)
    {
        if (isset($key) && is_string($key))
            $this[$key] = $value;
        else
            throw new \InvalidArgumentException("Invalid argument of [$key]");
    }
}

 