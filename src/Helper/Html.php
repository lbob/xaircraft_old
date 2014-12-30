<?php

namespace Xaircraft\Helper;
use Xaircraft\ERM\Entity;


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
        $result[] = '<a';
        $result[] = 'href="' . Url::link($url, $params) . '"';
        if (isset($id))
            $result[] = 'id="' . $id . '"';
        if (isset($class))
            $result[] = 'class="' . $class . '"';
        if (isset($attrs) && is_array($attrs)) {
            foreach ($attrs as $key => $attrValue) {
                if (is_string($key))
                    $result[] = $key . '="' . $attrValue . '"';
                else
                    $result[] = $attrValue;
            }
        }
        $result[] = '>' . $text . '</a>';
        return new Html($this->view, $this->html . implode(' ', $result));
    }

    public function beginTag($tagName, $args = null)
    {
        if (isset($tagName)) {
            $result[] = '<' . $tagName;
            if (isset($args)) {
                if (is_array($args)) {
                    $options = $args;
                    foreach ($options as $key => $value) {
                        if (is_string($key))
                            $result[] = $key . '="' . $value . '"';
                        else
                            $result[] = $value;
                    }
                    $result[] = '>';
                } else if (is_string($args)) {
                    $result[] = '>' . $args;
                }
            }
            return new Html($this->view, $this->html . implode(' ', $result));
        }
        return $this;
    }

    public function endTag($tagName)
    {
        if (isset($tagName)) {
            return new Html($this->view, $this->html . '</' . $tagName . '>');
        }
        return $this;
    }

    public function textBox($name, $value = null, $class = null, $attrs = null)
    {
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
            foreach ($attrs as $key => $attrValue) {
                if (is_string($key))
                    $result[] = $key . '="' . $attrValue . '"';
                else
                    $result[] = $attrValue;
            }
        }
        $result[] = '/>';
        return new Html($this->view, $this->html . implode(' ', $result));
    }

    public function textArea($name, $value = null, $class = null, $attrs = null)
    {
        $result[] = '<textarea';
        $result[] = 'id="' . $name . '" name="' . $name . '"';
        if (isset($class))
            $result[] = 'class="' . $class . '"';
        if (isset($attrs) && is_array($attrs)) {
            foreach ($attrs as $key => $attrValue) {
                if (is_string($key))
                    $result[] = $key . '="' . $attrValue . '"';
                else
                    $result[] = $attrValue;
            }
        }
        if (isset($value))
            $result[] = '>' . $value . '</textarea>';
        else if (isset($this->view)) {
            $result[] = '>' . $this->view->req->post($name) . '</textarea>';
        }
        return new Html($this->view, $this->html . implode(' ', $result));
    }

    public function formStart($name, $action, array $params, $method = 'post', $enctype = null, array $options = null)
    {
        $result[] = '<form';
        $result[] = 'id="' . $name . '" name="' . $name . '"';
        $result[] = 'action="' . Url::link($action, $params) . '"';
        $result[] = 'method="' . $method . '"';
        if (isset($enctype))
            $result[] = 'enctype="' . $enctype . '"';
        if (isset($options) && is_array($options)) {
            foreach ($options as $key => $attrValue) {
                if (is_string($key))
                    $result[] = $key . '="' . $attrValue . '"';
                else
                    $result[] = $attrValue;
            }
        }
        $result[] = '>';
        return new Html($this->view, $this->html . implode(' ', $result));
    }

    public function formEnd()
    {
        return new Html($this->view, $this->html . '</form>');
    }

    public function form(Entity $entity, $params = null, array $options = null)
    {
        $controller = $this->view->req->param('controller');
        $action = $this->view->req->param('action');
        $result[] = $this->formStart($entity->logicTableName, $controller . '/' . $action, $this->view->req->params(), 'post', null, $options) . '';
        if (isset($params)) {
            if (isset($params['show'])) {
                $showColumns = $params['show'];
                $result[] = $this->parseFormColumns($entity, $showColumns);
            }
        }
        $result[] = $this->submit() . '';
        $result[] = $this->formEnd() . '';
        return new Html($this->view, $this->html . implode(' ', $result));
    }

    private function parseFormColumns(Entity $entity, $columns)
    {
        if (isset($columns)) {
            $result = array();
            $result[] = '<table>';
            $entityData = $entity->getData();
            foreach ($columns as $key => $column) {
                $result[] = $this->parseFormColumn($entity->getFormColumnName($key), $key, $column, isset($entityData[$key]) ? $entityData[$key] : null);
            }
            $result[] = '</table>';
            return implode(' ', $result);
        }
    }

    private function parseFormColumn($formKey, $key, $column, $value)
    {
        if (isset($column) && is_array($column)) {
            if (isset($column[0])) {
                $type = $column[0];
            }
            if (isset($column[1])) {
                $title = $column[1];
            }
            if (isset($column[2])) {
                $options = $column[2];
                if (isset($options) && is_array($options)) {
                    $class = isset($options['class']) ? $options['class'] : null;
                    $attrs = isset($options['attrs']) ? $options['attrs'] : null;
                }
            }
            $formItem = '';
            switch (strtolower($type)) {
                case 'textbox':
                    $formItem = $this->textBox($formKey, $value, isset($class) ? $class : null, isset($attrs) ? $attrs : null) . '';
                    break;
                case 'textarea':
                    $formItem = $this->textArea($formKey, $value, isset($class) ? $class : null, isset($attrs) ? $attrs : null) . '';
                    break;
            }
            return '<tr><td>' . (isset($title) ? $title : $key) . '</td><td>' . $formItem . '</td></tr>';
        }
    }

    public function submit($name = null)
    {
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

 