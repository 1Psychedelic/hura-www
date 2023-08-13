<?php

namespace Hafo\Google\UI\AdSense;

class AdSenseControlFactory {

    private $adClient;

    private $state;

    function __construct($adClient, $state) {
        $this->adClient = $adClient;
        $this->state = $state;
    }

    function create($adSlot, $width, $height, $classes) {
        return new AdSenseControl($this->adClient, $adSlot, $width, $height, $classes, $this->state);
    }

}
