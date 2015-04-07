<?php

/**
 * Class Presentation
 *
 * @author lbob created at 2015/4/7 19:30
 */
class Presentation
{
    /**
     * @var Transport
     */
    private $transport;

    private $unPackedHandler;

    public function __construct(Transport $transport)
    {
        $this->transport = $transport;

        $this->transport->registerReceivedHandler(function ($data) {
            $this->unPack($data);

            var_dump('Presentation: ' . $data);
        });
    }

    public function pack(AirkeyModel $data)
    {
        //TODO: Pack the data.

        $this->transport->send($data);
    }

    public function registerUnPackedHandler(callable $handler)
    {
        $this->unPackedHandler = $handler;
    }

    private function unPack($data)
    {
        //TODO: UnPack the data.

        if (isset($this->unPackedHandler)) {
            call_user_func($this->unPackedHandler, $data);
        }
    }
}

 