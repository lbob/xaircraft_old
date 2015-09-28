<?php

namespace WeChat;
use Xaircraft\Plugins\WeChat\Corporation;


/**
 * Class DeepBlueCorporation
 *
 * @package WeChat
 * @author skyweo created at 2015/9/24 17:14
 */
class DeepBlueCorporation extends Corporation {

    public function getCorpName()
    {
        return 'default';
    }

    public function getCorpSecretConfig()
    {
        return array(
            'corpid' => 'wx3000e4fd5893321e',
            'corpsecret' => 'qFK1ae_mofhKwfAzyhEREBMYgYPoILqRFkAZnw4aGOeHt0agZ8lG3rtuTimhvPfs'
        );
    }
}

