<?php

namespace VCD\Admin\Pages\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use VCD\UI\FrontModule\WebModule\PagePresenter;

class PagesControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
        
        $this->onAnchor[] = function() {
            
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->list = $this->container->get(Context::class)->table('vcd_page')->order('special DESC');
        $this->template->pageLink = PagePresenter::LINK_DEFAULT;
        $this->template->render();
    }

}
