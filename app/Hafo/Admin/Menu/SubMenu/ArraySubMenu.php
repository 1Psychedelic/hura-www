<?php

namespace Hafo\Admin\Menu\SubMenu;

use Hafo\Admin\Menu;
use Nette\Utils\Html;

class ArraySubMenu implements Menu\SubMenu {

    private $topMenu;

    private $label;

    private $items = [];

    function __construct(Menu\TopMenu $topMenu, $label) {
        $this->topMenu = $topMenu;
        $this->label = $label;
    }

    function addItem($label, $href, $color, Html $icon, $information = NULL, $informationClass = 'danger', $targetBlank = FALSE) {
        $item = new Menu\MenuItem\SimpleMenuItem($label, $href, $color, $icon, $information, $informationClass, $targetBlank);
        $this->items[] = $item;
        return $this;
    }

    /**
     * @return Menu\MenuItem[]
     */
    function getItems() {
        return $this->items;
    }

    function getLabel() {
        return $this->label;
    }

    /**
     * @return Menu\TopMenu\ArrayTopMenu|Menu\TopMenu
     */
    function endSubMenu() {
        return $this->topMenu;
    }

    function count() {
        return count($this->items);
    }

    function needsAttention() {
        foreach($this->getItems() as $item) {
            if($item->getInformation() && $item->getInformationClass() === 'danger') {
                return TRUE;
            }
        }
        return FALSE;
    }

}
