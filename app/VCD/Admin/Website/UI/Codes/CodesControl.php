<?php

namespace VCD\Admin\Website\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use VCD\Admin\Website\UI\CodeControl;

class CodesControl extends Control {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    function handleCodeUp($id) {
        $row = $this->db()->table('vcd_web_code')->wherePrimary($id)->fetch();
        $prev = $this->db()->table('vcd_web_code')->where('position < ?', $row['position'])->order('position DESC')->fetch();
        if($prev) {
            $this->db()->table('vcd_web_code')->wherePrimary($prev['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_web_code')->wherePrimary($id)->update(['position' => $prev['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function handleCodeDown($id) {
        $row = $this->db()->table('vcd_web_code')->wherePrimary($id)->fetch();
        $next = $this->db()->table('vcd_web_code')->where('position > ?', $row['position'])->order('position ASC')->fetch();
        if($next) {
            $this->db()->table('vcd_web_code')->wherePrimary($next['id'])->update(['position' => $row['position']]);
            $this->db()->table('vcd_web_code')->wherePrimary($id)->update(['position' => $next['position']]);
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->codes = $this->db()->table('vcd_web_code')->order('position ASC');
        $this->template->visibility = function($visibility) {
            return CodeControl::$visibility[$visibility];
        };
        $this->template->render();
    }

    private function db() {
        return $this->container->get(Context::class);
    }

}
