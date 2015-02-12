<?php

namespace Xaircraft;
use Xaircraft\Common\Net;
use Xaircraft\Config\Inject;
use Xaircraft\Http\Response;


/**
 * Class App
 *
 * @package Xaircraft
 * @author lbob created at 2014/12/6 11:08
 * @website https://bitbucket.org/skyweo/xaircraft
 */
class App extends Container {

    const FRAMEWORK = 'Xaircraft';
    const VERSION = '0.1';
    const ENV_MODE = 'mode';
    const ENV_VIEW_FILE_EXT = 'view_file_ext';
    const ENV_DEFAULT_TOKEN = 'default_token';
    const ENV_SESSION_PROVIDER = 'session_provider';
    const ENV_DATABASE_PROVIDER = 'database_provider';
    const APP_MODE_DEV = 'dev';
    const APP_MODE_PUB = 'pub';
    const APP_MODE_TEST = 'test';
    const HOST = 'host';
    const APP_RUNTIME_MODE_CLI = 'cli';
    const APP_RUNTIME_MODE_APACHE2HANDLER = 'apache2handler';
    const OS_WIN = 'os_win';
    const OS_LINUX = 'os_linux';

    /**
     * @var $req Http\Request;
     */
    public $req;
    public $response;
    public $environment = array();

    private $startHandlers = array();
    private $endHandlers = array();
    private $errorHandlers = array();
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

    /**
     * @var \Xaircraft\Session\UserSession
     */
    private $userSession;

    private $runtimeMode;
    /**
     * @var string 当前操作系统类型
     */
    private $os;
    private $osInfo;

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
        $this->runtimeMode = php_sapi_name();
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
        try {
            $this->onStart();
            $this->autoload();
            $this->inject();
            $this->routing();
            $this->onEnd();
        } catch (\Exception $ex) {
            if ((!isset($this->errorHandlers) || empty($this->errorHandlers)) || $this->environment[self::ENV_MODE] === self::APP_MODE_DEV) {
                throw $ex;
            } else {
                $this->onError($ex);
            }
        }
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

    private function inject()
    {
        Inject::load();
        $path = App::path('inject');
        if (isset($path)) {
            if (is_file($path) && is_readable($path)) {
                require $path;
            }
        }
    }

    private function autoload()
    {
        $this->classLoader = new ClassLoader();
        $autoloadConfigFilePath = \Xaircraft\App::path("autoload");
        if (is_file($autoloadConfigFilePath) && is_readable($autoloadConfigFilePath)) {
            $autoloadDirs = require $autoloadConfigFilePath;
            $paths = array();
            if (isset($autoloadDirs) && is_array($autoloadDirs)) {
                foreach ($autoloadDirs as $item) {
                    $paths[] = \Xaircraft\App::path("app").$item;
                }
                $this->classLoader->addPaths($paths);
            }
        }
        $this->classLoader->addPath(\Xaircraft\App::path("app").'/models');
    }

    private function routing()
    {
        if ($this->environment[self::ENV_MODE] === self::APP_MODE_TEST || $this->getRuntimeMode() === self::APP_RUNTIME_MODE_CLI) {
            return;
        }
        $this->router = \Nebula\Router::getInstance($this->paths['routes'], $this->paths['filter']);
        $this->router->baseMappings['default']['default'] = $this->environment[self::ENV_DEFAULT_TOKEN];

        $this->router->registerMatchedHandler(function ($params) {
            App::getInstance()->req = Http\Request::getInstance($params);
            App::getInstance()->response = new Response();
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

    public function registerErrorHandler($handler)
    {
        if (isset($handler) && is_callable($handler)) {
            $this->errorHandlers[] = $handler;
        }
    }

    public function end()
    {
        $this->onEnd();
        exit;
    }

    public function getClientIP()
    {
        return Net::getClientIP();
    }

    public function getServerIP()
    {
        return Net::getServerIP();
    }

    /**
     * @return \Xaircraft\Session\UserSession
     */
    public function getUserSession()
    {
        if (!isset($this->userSession)) {
            $this->userSession = self::get('UserSession');
        }

        if (isset($this->userSession)) {
            return $this->userSession;
        }

        return null;
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

    private function onError($ex)
    {
        if (isset($this->errorHandlers)) {
            foreach ($this->errorHandlers as $handler) {
                $handler($this, $ex);
            }
        }
    }

    public static function bind($interface, $implement, array $params = null)
    {
        DI::getInstance()->bind($interface, $implement, $params);
    }

    public static function bindSingleton($interface, $implement = null, array $params = null)
    {
        DI::getInstance()->bindSingleton($interface, $implement, $params);
    }

    public static function bindParam($interface, array $params)
    {
        DI::getInstance()->bindParam($interface, $params);
    }

    public static function get($interface, array $params = null)
    {
        return DI::getInstance()->get($interface, $params);
    }

    public function getRuntimeMode()
    {
        return $this->runtimeMode;
    }

    public function getOS()
    {
        if (!isset($this->os)) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $this->os = self::OS_WIN;
            } else {
                $this->os = self::OS_LINUX;
            }
            $this->osInfo = php_uname();
        }
        return $this->os;
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

 