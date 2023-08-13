<?php

namespace VCD\Admin\Events\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nextras\Orm\Collection\ICollection;
use VCD2\Orm;

class EventStepOptionsControl extends Control {

    private $container;

    private $event;

    private $step;

    function __construct(ContainerInterface $container, $event, $step) {
        $this->container = $container;
        $this->event = $event;
        $this->step = $step;
    }

    function handleUp($id) {
        $row = $this->db()->table('vcd_event_step_option')->wherePrimary($id)->where('step', $this->step)->fetch();
        $prev = $this->db()->table('vcd_event_step_option')->where('step', $this->step)->where('position < ?', $row['position'])->order('position DESC')->fetch();
        if($prev) {
            $this->db()->table('vcd_event_step_option')->wherePrimary($prev['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_event_step_option')->wherePrimary($id)->update(['position' => $prev['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function handleDown($id) {
        $row = $this->db()->table('vcd_event_step_option')->wherePrimary($id)->where('step', $this->step)->fetch();
        $next = $this->db()->table('vcd_event_step_option')->where('step', $this->step)->where('position > ?', $row['position'])->order('position ASC')->fetch();
        if($next) {
            $this->db()->table('vcd_event_step_option')->wherePrimary($next['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_event_step_option')->wherePrimary($id)->update(['position' => $next['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->list = $this->container->get(Orm::class)->eventStepOptions->findBy(['this->step->id' => $this->step])->orderBy('position', ICollection::ASC);
        //$this->template->list = $this->container->get(Context::class)->table('vcd_event_step_option')->where('step', $this->step)->order('position ASC')->fetchAll();
        $this->template->event = $this->event;
        $this->template->step = $this->step;
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
