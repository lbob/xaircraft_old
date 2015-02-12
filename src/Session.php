<?php

namespace Xaircraft;
use Xaircraft\Session\FileSessionProvider;
use Xaircraft\Session\SessionProvider;

/**
 * Class Session
 *
 * @author skyweo created at 14/12/17 17:13
 */
class Session 
{
    /**
     * @var Session
     */
    private static $instance;

    /**
     * @var SessionProvider
     */
    protected $provider;

    private function __construct(SessionProvider $provider)
    {
        $this->provider = $provider;
    }

    private static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = App::get('Xaircraft\Session\SessionProvider');
        return self::$instance;
    }

    public static function put($key, $value)
    {
        self::getInstance()->provider->put($key, $value);
    }

    public static function push($key, $value)
    {
        self::getInstance()->provider->push($key, $value);
    }

    public static function get($key, $default = null)
    {
        return self::getInstance()->provider->get($key, $default);
    }

    public static function pull($key)
    {
        return self::getInstance()->provider->pull($key);
    }

    public static function all()
    {
        return self::getInstance()->provider->all();
    }

    public static function has($key)
    {
        return self::getInstance()->provider->has($key);
    }

    public static function forget($key)
    {
        self::getInstance()->provider->forget($key);
    }

    public static function flush()
    {
        self::getInstance()->provider->flush();
    }

    public static function regenerate()
    {
        return self::getInstance()->provider->regenerate();
    }

    public static function flash($key, $value)
    {
        return self::getInstance()->provider->flash($key, $value);
    }

    public static function reflash($key)
    {
        return self::getInstance()->provider->reflash($key);
    }

    public static function remeber($key)
    {
        return self::getInstance()->provider->remeber($key);
    }
}

 