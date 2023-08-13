<?php

namespace VCD\Admin\Ebooks\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class EbooksControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
        
        $this->onAnchor[] = function() {
            
        };
    }

    function handleUp($id) {
        $row = $this->db()->table('vcd_ebook')->wherePrimary($id)->fetch();
        $prev = $this->db()->table('vcd_ebook')->where('position < ?', $row['position'])->order('position DESC')->fetch();
        if($prev) {
            $this->db()->table('vcd_ebook')->wherePrimary($prev['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_ebook')->wherePrimary($id)->update(['position' => $prev['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function handleDown($id) {
        $row = $this->db()->table('vcd_ebook')->wherePrimary($id)->fetch();
        $next = $this->db()->table('vcd_ebook')->where('position > ?', $row['position'])->order('position ASC')->fetch();
        if($next) {
            $this->db()->table('vcd_ebook')->wherePrimary($next['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_ebook')->wherePrimary($id)->update(['position' => $next['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->list = $this->container->get(Context::class)->table('vcd_ebook')->order('position ASC');
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
