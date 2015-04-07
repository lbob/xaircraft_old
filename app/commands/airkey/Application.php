<?php

/**
 * Class Application
 *
 * @author lbob created at 2015/4/7 19:56
 */
class Application {

    /**
     * @var Presentation
     */
    private $presentation;

    private $receivedHandler;

    public function __construct(Presentation $presentation)
    {
        $this->presentation = $presentation;

        $this->presentation->registerUnPackedHandler(function ($data) {
            if (isset($this->receivedHandler)) {
                call_user_func($this->receivedHandler, $data);
            }

            var_dump('Application: ' . $data);
        });
    }

    public function send(AirkeyModel $data)
    {
        $this->presentation->pack($data);
    }

    public function registerReceivedHandler(callable $handler)
    {
        if (isset($handler)) {
            $this->receivedHandler = $handler;
        }
    }
}

 