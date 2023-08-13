<?php

namespace Hafo\Admin\Menu\UI;

use Hafo\Admin\Menu\TopMenu;
use Nette\Application\UI\Control;

class MenuControl extends Control {

    private $menu;

    function __construct(TopMenu $menu) {
        $this->menu = $menu;
    }

    function render() {
        $this->template->menu = $this->menu;
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
