<?php

namespace VCD\Admin\Events\UI;

use Hafo\DI\Container;
use Nette\Application\UI\Control;
use VCD\Admin\Applications\UI\ApplicationsFiltersControl;
use VCD2\Orm;

class EventDiscountsControl extends Control {

    function __construct(Container $container, $event) {

        $orm = $container->get(Orm::class);
        $this->onAnchor[] = function () use ($event, $orm) {

            $this->template->list = $orm->discounts->findBy(['event' => $event]);
            $this->template->event = $event;
            $this->template->eventEntity = $orm->events->get($event);
            $this->template->statusAllExceptUnfinished = ApplicationsFiltersControl::STATUS_ALL_EXCEPT_UNFINISHED;
        };

    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }


}
