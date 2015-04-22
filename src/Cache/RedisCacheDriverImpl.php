<?php

namespace Xaircraft\Cache;
use Xaircraft\Storage\Redis;


/**
 * Class RedisCacheProviderImpl
 *
 * @package Xaircraft\Cache
 * @author lbob created at 2015/1/17 14:42
 */
class RedisCacheDriverImpl implements CacheDriver {


    /**
     * 存储数据到缓存中
     * @param $key
     * @param $value
     * @param $minutes
     * @return mixed
     */
    public function put($key, $value, $minutes)
    {
        Redis::getInstance()->setex($key, $minutes * 60, $value);
    }

    /**
     * 检查缓存是否存在
     * @param $key
     * @return mixed
     */
    public function has($key)
    {
        return Redis::getInstance()->exists($key);
    }

    /**
     * 从缓存中取得数据，若数据为空则回传默认值
     * @param $key
     * @param $defaultValue
     * @return mixed
     */
    public function get($key, $defaultValue = null)
    {
        $value = Redis::getInstance()->get($key);

        if (!isset($value)) {
            if (is_callable($defaultValue)) {
                $value = call_user_func($defaultValue);
            } else {
                $value = $defaultValue;
            }
        }

        return $value;
    }

    /**
     * 永久存储数据到缓存中
     * @param $key
     * @param $value
     * @return mixed
     */
    public function forever($key, $value)
    {
        Redis::getInstance()->set($key, $value);
    }

    /**
     * 从缓存中取得数据，当数据不存在时会存储默认值
     * @param $key
     * @param $minutes
     * @param $defaultValue
     * @return mixed
     */
    public function remember($key, $minutes, $defaultValue)
    {
        $value = Redis::getInstance()->get($key);

        if (!isset($value)) {
            if (is_callable($defaultValue)) {
                $value = call_user_func($defaultValue);
            } else {
                $value = $defaultValue;
            }
            Redis::getInstance()->setex($key, $minutes * 60, $value);
        }

        return $value;
    }

    /**
     * 从缓存中取得数据，当数据不存在时会永久存储默认值
     * @param $key
     * @param $defaultValue
     * @return mixed
     */
    public function rememberForever($key, $defaultValue)
    {
        $value = Redis::getInstance()->get($key);

        if (!isset($value)) {
            if (is_callable($defaultValue)) {
                $value = call_user_func($defaultValue);
            } else {
                $value = $defaultValue;
            }
            Redis::getInstance()->set($key, $value);
        }

        return $value;
    }

    /**
     * 从缓存中取得数据并删除缓存
     * @param $key
     * @return mixed
     */
    public function pull($key)
    {
        $value = Redis::getInstance()->get($key);
        Redis::getInstance()->del(array($key));
        return $value;
    }

    /**
     * 从缓存中删除数据
     * @param $key
     * @return mixed
     */
    public function forget($key)
    {
        Redis::getInstance()->del(array($key));
    }

    /**
     * 递增值
     * @param $key
     * @param int $amount
     * @return mixed
     */
    public function increment($key, $amount = 1)
    {
        // TODO: Implement decrement() method.
    }

    /**
     * 递减值
     * @param $key
     * @param int $amount
     * @return mixed
     */
    public function decrement($key, $amount = 1)
    {
        // TODO: Implement decrement() method.
    }
}

 