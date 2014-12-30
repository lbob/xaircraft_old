<?php

namespace Xaircraft\Mvc;
use Xaircraft\App;


/**
 * Class PjaxContainer
 *
 * @package Xaircraft\Mvc
 * @author lbob created at 2014/12/30 10:43
 */
class PjaxContainer {

    private $view;
    public $options = array();
    public $clientOptions = array();

    public $linkSelector;
    public $formSelector;
    public $enablePushState = true;
    public $enableReplaceState = false;
    public $timeout = 1000;
    public $scrollTo = false;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function begin($id)
    {
        $this->options['id'] = $id;

        if ($this->view->req->isPJAX()) {
            $this->view->response->clear();
            if (isset($this->view->data['title'])) {
                echo $this->view->html()->beginTag('title', $this->view->data['title'])->endTag('title');
            }
        } else {
            echo $this->view->html()->beginTag('div', $this->options);
        }
    }

    public function end()
    {
        if (!$this->view->req->isPJAX()) {
            echo $this->view->html()->endTag('div');
            $this->registerClientScript();

            return;
        }
        $content = $this->view->response->getOriginalContent();
        $this->view->response->setContent($content);
        $this->view->response->flush();
        App::getInstance()->end();
    }

    private function registerClientScript()
    {
        $id = $this->options['id'];
        $this->clientOptions['push'] = $this->enablePushState;
        $this->clientOptions['replace'] = $this->enableReplaceState;
        $this->clientOptions['timeout'] = $this->timeout;
        $this->clientOptions['scrollTo'] = $this->scrollTo;
        $options = json_encode($this->clientOptions);
        $linkSelector = json_encode($this->linkSelector !== null ? $this->linkSelector : '#' . $id . ' a');
        $formSelector = json_encode($this->formSelector !== null ? $this->formSelector : '#' . $id . ' form[data-pjax]');
        $js = "jQuery(document).pjax($linkSelector, \"#$id\", $options);";
        $js .= "\njQuery(document).on('submit', $formSelector, function (event) {jQuery.pjax.submit(event, '#$id', $options);});";
        $this->view->registerJs($js);
    }
}

 