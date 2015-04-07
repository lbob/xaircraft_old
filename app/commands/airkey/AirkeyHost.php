<?php

/**
 * Class AirkeyHost
 *
 * @author lbob created at 2015/4/7 19:57
 */
class AirkeyHost extends \Xaircraft\Core\Cli\Command {

    /**
     * @var Application
     */
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->application->registerReceivedHandler(function ($data) {
            var_dump("AirkeyHost: " . $data);
        });
    }

    public function execute()
    {
        var_dump('begin');
        $model = new AirkeyModel();
        $model->data = array(1, 2, 3);
        $model->dev_id = '1234567890';
        $model->token = 'asdfasdf';
        $this->application->send($model);
        var_dump('end');
    }
}

 