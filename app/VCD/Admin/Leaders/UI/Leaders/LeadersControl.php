<?php

namespace VCD\Admin\Leaders\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class LeadersControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    function handleLeaderUp($id) {
        $row = $this->db()->table('vcd_leader')->wherePrimary($id)->fetch();
        $prev = $this->db()->table('vcd_leader')->where('position < ?', $row['position'])->order('position DESC')->fetch();
        if($prev) {
            $this->db()->table('vcd_leader')->wherePrimary($prev['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_leader')->wherePrimary($id)->update(['position' => $prev['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function handleLeaderDown($id) {
        $row = $this->db()->table('vcd_leader')->wherePrimary($id)->fetch();
        $next = $this->db()->table('vcd_leader')->where('position > ?', $row['position'])->order('position ASC')->fetch();
        if($next) {
            $this->db()->table('vcd_leader')->wherePrimary($next['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_leader')->wherePrimary($id)->update(['position' => $next['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $selection = $this->db()->table('vcd_leader');
        $this->template->leaders = $selection->order('position ASC');
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
