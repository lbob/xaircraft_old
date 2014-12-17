<?php

namespace Xaircraft\Session;


/**
 * Class FileSessionProvider
 *
 * @package Xaircraft\Session
 * @author skyweo created at 14/12/17 19:41
 */
class FileSessionProvider implements SessionProvider
{
    public function __construct()
    {
        session_start();
    }

    public function put($key, $value)
    {
        if (isset($key)) {
            $_SESSION[$key] = $value;
        }
    }

    public function push($key, $value)
    {
        if (isset($key)) {
            $_SESSION[$key][] = $value;
        }
    }

    public function get($key, $default = null)
    {
        if (isset($key) && isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else if (isset($default)) {
            if (is_callable($default)) {
                return call_user_func($default);
            } else {
                return $default;
            }
        }
        return null;
    }

    public function pull($key)
    {
        if (isset($key)) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $value;
        }
        return null;
    }

    public function all()
    {
        return $_SESSION;
    }

    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public function forget($key)
    {
        if (isset($key)) {
            unset($_SESSION[$key]);
        }
    }

    public function flush()
    {
        unset($_SESSION);
    }

    public function regenerate()
    {
        return session_regenerate_id();
    }
}

 