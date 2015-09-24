<?php

namespace WeChat;


/**
 * Class Message
 *
 * @package WeChat
 * @author skyweo created at 2015/9/24 17:16
 */
class Message extends DeepBlueAIApp {

    private $API = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token={ACCESS_TOKEN}';

    public function sendText()
    {
        $body = array(
            "touser" => "skyweo",
            "agentid" => $this->getAppID(),
            "msgtype" => "text",
            "text" => array(
                "content" => "Hello World!"
            ),
            "safe" => 0
        );

        $this->post($this->API, $this->formatBody($body));
    }
}

