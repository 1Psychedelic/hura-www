<?php

namespace Hafo\Admin\Menu\MenuItem;

use Hafo\Admin\Menu;
use Nette\Utils\Html;

class SimpleMenuItem implements Menu\MenuItem {

    private $label;

    private $href;

    private $color;

    private $icon;

    private $information;

    private $informationClass;

    private $targetBlank;

    function __construct($label, $href, $color, Html $icon, $information = NULL, $informationClass = 'danger', $targetBlank = FALSE) {
        $this->label = $label;
        $this->href = $href;
        $this->color = $color;
        $this->icon = $icon;
        $this->information = $information;
        $this->informationClass = $informationClass;
        $this->targetBlank = $targetBlank;
    }

    function getLabel() {
        return $this->label;
    }

    function getHref() {
        return $this->href;
    }

    function getColor() {
        return $this->color;
    }

    function getIcon() {
        return $this->icon;
    }

    function getInformation() {
        return $this->information;
    }

    function getInformationClass() {
        return $this->informationClass;
    }

    function getTargetBlank() {
        return $this->targetBlank;
    }

}
