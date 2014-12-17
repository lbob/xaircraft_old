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
            self::$instance = self::create(App::getInstance()->environment[App::ENV_SESSION_PROVIDER]);
        return self::$instance;
    }

    private static function create($provider)
    {
        switch (strtolower($provider)) {
            case 'file':
                return new Session(new FileSessionProvider());
            default:
                return new Session(new FileSessionProvider());
        }
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
}

 