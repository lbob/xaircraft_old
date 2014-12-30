<?php

namespace Xaircraft\Http;


/**
 * Class Response
 *
 * @package Xaircraft\Http
 * @author lbob created at 2014/12/30 14:16
 */
class Response {

    private $content;

    public function __construct()
    {
        ob_start();
        ob_implicit_flush(false);
    }

    public function getOriginalContent()
    {
        $this->content = ob_get_clean();
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function flush()
    {
        echo $this->content;
        flush();
    }

    public function clear()
    {
        ob_clean();
    }

    public function setStatusCode($statusCode)
    {
        http_response_code($statusCode);
    }
}

 