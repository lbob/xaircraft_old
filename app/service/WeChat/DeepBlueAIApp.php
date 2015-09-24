<?php

namespace WeChat;
use Xaircraft\App;
use Xaircraft\Plugins\WeChat\Application;


/**
 * Class DeepBlueAIApp
 *
 * @package WeChat
 * @author skyweo created at 2015/9/24 17:14
 */
class DeepBlueAIApp extends Application {

    public function getCorporation()
    {
        return App::get(DeepBlueCorporation::class);
    }

    public function getAppID()
    {
        return 1;
    }
}

