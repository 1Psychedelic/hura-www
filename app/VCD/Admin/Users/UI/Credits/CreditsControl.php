<?php

namespace VCD\Admin\Users\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use VCD2\Orm;

class CreditsControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $showAll = FALSE) {
        $this->container = $container;
        
        $this->onAnchor[] = function() use ($showAll) {

            $orm = $this->container->get(Orm::class);

            $this->template->circulatingCredits = $orm->credits->getCirculatingValue();
            
            $this->template->list = $orm->creditMovements->findAll();

            $this->template->showAll = $showAll;

        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
