<?php

namespace Hafo\Admin\Menu\TopMenu;

use Hafo\Admin\Menu;
use Hafo\Admin\Menu\SubMenu;

class ArrayTopMenu implements Menu\TopMenu {

    private $items = [];

    /**
     * @param string $label
     * @return SubMenu\ArraySubMenu
     */
    function addSubMenu($label) {
        $submenu = new SubMenu\ArraySubMenu($this, $label);
        $this->items[] = $submenu;
        return $submenu;
    }

    function getItems() {
        return $this->items;
    }

    function count() {
        return count($this->items);
    }

}
