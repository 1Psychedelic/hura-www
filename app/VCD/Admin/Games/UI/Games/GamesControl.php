<?php

namespace VCD\Admin\Games\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class GamesControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
        
        $this->onAnchor[] = function() {
            
        };
    }

    function handleUp($id) {
        $row = $this->db()->table('vcd_game')->wherePrimary($id)->fetch();
        $prev = $this->db()->table('vcd_game')->where('position < ?', $row['position'])->order('position DESC')->fetch();
        if($prev) {
            $this->db()->table('vcd_game')->wherePrimary($prev['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_game')->wherePrimary($id)->update(['position' => $prev['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function handleDown($id) {
        $row = $this->db()->table('vcd_game')->wherePrimary($id)->fetch();
        $next = $this->db()->table('vcd_game')->where('position > ?', $row['position'])->order('position ASC')->fetch();
        if($next) {
            $this->db()->table('vcd_game')->wherePrimary($next['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_game')->wherePrimary($id)->update(['position' => $next['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->list = $this->container->get(Context::class)->table('vcd_game')->order('position ASC');
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
