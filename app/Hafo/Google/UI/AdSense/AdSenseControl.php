<?php

namespace Hafo\Google\UI\AdSense;

use Nette\Application\UI\Control;

class AdSenseControl extends Control {

    const STATE_DISABLED = 0;
    const STATE_ENABLED = 1;
    const STATE_TEST_GOOGLE = 2;
    const STATE_TEST_LOCAL = 3;

    private $adClient;

    private $adSlot;

    private $width;

    private $height;

    private $classes;

    private $state;

    function __construct($adClient, $adSlot, $width, $height, $classes, $state) {
        $this->adClient = $adClient;
        $this->adSlot = $adSlot;
        $this->width = $width;
        $this->height = $height;
        $this->classes = $classes;
        $this->state = $state;
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->adClient = $this->adClient;
        $this->template->adSlot = $this->adSlot;
        $this->template->width = $this->width;
        $this->template->height = $this->height;
        $this->template->classes = $this->classes;
        $this->template->state = $this->state;
        $this->template->render();
    }

}
