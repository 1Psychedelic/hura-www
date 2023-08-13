<?php

namespace VCD\Admin\Events\UI;

use Hafo\Persona\Gender;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nextras\Orm\Collection\ICollection;
use VCD2\Events\Event;
use VCD2\Orm;

class EventsControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $filters = [], $past = FALSE) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($filters, $past) {

            $orm = $this->container->get(Orm::class);
            
            $cond = [];
            foreach($filters as $key => $val) {
                if($val === '-1') {
                    $cond[$key . '!='] = NULL;
                } else if($val === 2) {
                    $cond[$key] = NULL;
                } else {
                    $cond[$key] = $val;
                }
            }

            /** @var ICollection|Event[] $events */
            $events = $past
                ? $orm->events->findAll()->orderBy('starts', ICollection::DESC)
                : $orm->events->findUpcoming(NULL, TRUE)->orderBy('starts', ICollection::ASC);

            if (!empty($cond)) {
                $events = $events->findBy($cond);
            }

            $this->template->filters = $filters;
            $this->template->past = $past;
            $this->template->events = $events;
            $this->template->typeTrip = Event::TYPE_TRIP;
            $this->template->typeCamp = Event::TYPE_CAMP;
            $this->template->types = Event::TYPES_IDS;
            $this->template->male = Gender::MALE;
            $this->template->female = Gender::FEMALE;
            $this->template->tabs = function($id) {
                return $this->db()->table('vcd_event_tab')->where('event', $id)->select('COUNT(id)')->fetchField();
            };
            $this->template->photos = function($id = NULL, $type = 0) {
                return $this->db()->table('vcd_photo')->where('event', $id)->where('type', $type)->select('COUNT(id)')->fetchField();
            };
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
