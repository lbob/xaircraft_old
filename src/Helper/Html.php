<?php

namespace Xaircraft\Helper;


/**
 * Class Html
 *
 * @package Xaircraft\Helper
 * @author lbob created at 2014/12/7 20:22
 */
class Html {

    /**
     * @var \Xaircraft\Mvc\View
     */
    private $view;

    public function __construct($view = null)
    {
        $this->view = $view;
    }

    public function link($url, $text, $params = null, $id = null, $class = null, $attrs = null)
    {
        $result = array();
        $result[] = '<a';
        $result[] = 'href="' . Url::link($url, $params) . '"';
        if (isset($id))
            $result[] = 'id="' . $id . '"';
        if (isset($class))
            $result[] = 'class="' . $class . '"';
        if (isset($attrs) && is_array($attrs)) {
            foreach ($attrs as $key => $value) {
                $result[] = $key . '="' . $value . '"';
            }
        }
        $result[] = '>' . $text . '</a>';
        return implode(' ', $result);
    }

    public function textBox($name, $value = null, $class = null, $attrs = null)
    {
        $result = array();
        $result[] = '<input';
        $result[] = 'id="' . $name . '" name="' . $name . '"';
        if (isset($value))
            $result[] = 'value="' . $value . '"';
        else if (isset($this->view)) {
            $result[] = 'value="' . $this->view->req->post($name) . '"';
        }
        if (isset($class))
            $result[] = 'class="' . $class . '"';
        if (isset($attrs) && is_array($attrs)) {
            foreach ($attrs as $key => $value) {
                $result[] = $key . '="' . $value . '"';
            }
        }
        $result[] = '/>';
        return implode(' ', $result);
    }

    public function submit($name = null)
    {
        $result = array();
        $result[] = '<input type="submit"';
        if (isset($name))
            $result[] = 'id="' . $name . '_submit" name="' . $name . '_submit"';
        else
            $result[] = 'id="submit" name="submit"';
        $result[] = '/>';
        return implode(' ', $result);
    }
}

 