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

    private $html = '';

    public function __construct($view = null, $html = null)
    {
        $this->view = $view;
        if (isset($html))
            $this->html = $this->html . $html;
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
        return new Html($this->view, $this->html . implode(' ', $result));
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
        return new Html($this->view, $this->html . implode(' ', $result));
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
        return new Html($this->view, $this->html . implode(' ', $result));
    }

    public function render()
    {
        echo $this->html;
    }

    public function __toString()
    {
        return $this->html;
    }
}

 