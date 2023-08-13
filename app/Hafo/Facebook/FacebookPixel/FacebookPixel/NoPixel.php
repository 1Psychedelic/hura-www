<?php

namespace Hafo\Facebook\FacebookPixel\FacebookPixel;

class NoPixel implements \Hafo\Facebook\FacebookPixel\FacebookPixel {

    function addPurchase(array $data) {

    }

    function getTrackingHtml() {
        return NULL;
    }


}
