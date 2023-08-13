<?php

namespace VCD\Admin\Newsletter\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nextras\Orm\Collection\ICollection;
use VCD2\Events\Event;
use VCD2\Orm;

class AttendantsControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $divider = ';') {
        $this->container = $container;

        $this->onAnchor[] = function() use ($divider) {

            $orm = $this->container->get(Orm::class);

            $emails = [];

            /** @var Event $event */
            $events = $orm->events->findAll()->orderBy('ends', ICollection::DESC)->limitBy(10);
            foreach($events as $event) {

                $emails[$event->name] = [];

                foreach($event->acceptedApplications as $application) {
                    $emails[$event->name][$application->email] = TRUE;
                }

            }

            $this->template->data = $emails;
            $this->template->divider = $divider;

        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
