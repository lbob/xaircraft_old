<?php

namespace Xaircraft\Plugins\WeChat;
use Xaircraft\Common\Net;


/**
 * Class Request
 *
 * @package WeChat\Request
 * @author skyweo created at 2015/9/24 14:44
 */
class Request extends Net {

    public static function get($url, array $params = array())
    {
        $content = json_decode(parent::get($url, $params), true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception("WeChat/Request: JSON Parse ERROR.");
        }
        if (isset($content['errcode']) && intval($content['errcode']) > 0) {
            throw new \Exception('WeChat: ' . ErrorCode::getCodeInfo($content), $content['errcode']);
        }
        return $content;
    }

    public static function post($url, $body, array $params = null)
    {
        $content = json_decode(parent::post($url, $body, $params), true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception("WeChat/Request: JSON Parse ERROR.");
        }
        if (isset($content['errcode']) && intval($content['errcode']) > 0) {
            throw new \Exception('WeChat: ' . ErrorCode::getCodeInfo($content), $content['errcode']);
        }
        return $content;
    }

    public static function request($url, array $params = array())
    {
        return parent::get($url, $params);
    }
}

