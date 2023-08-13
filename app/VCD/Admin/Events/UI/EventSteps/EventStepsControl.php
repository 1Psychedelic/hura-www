<?php

namespace VCD\Admin\Events\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class EventStepsControl extends Control {

    private $container;

    private $event;

    function __construct(ContainerInterface $container, $event) {
        $this->container = $container;
        $this->event = $event;
        
        $this->onAnchor[] = function() {
            
        };
    }

    function handleUp($id) {
        $row = $this->db()->table('vcd_event_step')->wherePrimary($id)->where('event', $this->event)->fetch();
        $prev = $this->db()->table('vcd_event_step')->where('event', $this->event)->where('position < ?', $row['position'])->order('position DESC')->fetch();
        if($prev) {
            $this->db()->table('vcd_event_step')->wherePrimary($prev['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_event_step')->wherePrimary($id)->update(['position' => $prev['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function handleDown($id) {
        $row = $this->db()->table('vcd_event_step')->wherePrimary($id)->where('event', $this->event)->fetch();
        $next = $this->db()->table('vcd_event_step')->where('event', $this->event)->where('position > ?', $row['position'])->order('position ASC')->fetch();
        if($next) {
            $this->db()->table('vcd_event_step')->wherePrimary($next['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_event_step')->wherePrimary($id)->update(['position' => $next['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->list = $this->container->get(Context::class)->table('vcd_event_step')->where('event', $this->event)->order('position ASC')->fetchAll();
        $this->template->event = $this->event;
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
