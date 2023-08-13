<?php

namespace VCD\Admin\Applications\UI;

use Hafo\Persona\HumanAge;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use VCD2\Events\Event;
use VCD2\Orm;

class AcceptedChildrenControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id) {
            
            $orm = $this->container->get(Orm::class);
            
            /** @var Event $event */
            $event = $orm->events->get($id);

            $this->template->event = $event;
            $this->template->children = $event->acceptedChildren;
            $this->template->age = function($dateBorn) use ($event) {
                return (new HumanAge($dateBorn))->yearsAt($event->starts);
            };
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
