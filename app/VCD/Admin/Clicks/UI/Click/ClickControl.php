<?php

namespace VCD\Admin\Clicks\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class ClickControl extends Control {

    private $container;

    private $url;

    function __construct(ContainerInterface $container, $url) {
        $this->container = $container;
        $this->url = $url;
        
        $this->onAnchor[] = function() {
            
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->list = $this->container->get(Context::class)->table('vcd_click')->where('url', $this->url)->order('created_at DESC');
        $this->template->url = $this->url;
        $this->template->render();
    }

}
