<?php

namespace Hafo\NetteBridge\UI;

use Nette\Application\UI\Control;
use Nette\Utils\Html;

class HtmlControl extends Control {

    private $content;

    function __construct($content) {
        $this->content = $content;
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->content = Html::el()->setHtml($this->content);
        $this->template->render();
    }

}
