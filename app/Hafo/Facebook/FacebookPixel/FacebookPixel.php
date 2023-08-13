<?php

namespace Hafo\Facebook\FacebookPixel;

interface FacebookPixel {

    function addPurchase(array $data);

    function getTrackingHtml();

}
