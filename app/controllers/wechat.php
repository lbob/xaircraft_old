<?php
use WeChat\DeepBlueAIApp;

/**
 * Class wechat
 *
 * @author skyweo created at 2015/9/24 17:23
 */
class wechat_controller extends \Xaircraft\Mvc\Controller {

    public function index()
    {
        $message = new \Xaircraft\Plugins\WeChat\API\Message(\Xaircraft\App::get(DeepBlueAIApp::class));
        $message->sendText("test", "@all");
    }
}

