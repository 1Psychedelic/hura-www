<?php

namespace Hafo\Google\ConversionTracking\Tracker;

class NoTracker implements \Hafo\Google\ConversionTracking\Tracker {

    function addConversion($name, $conversionId, $transactionId = NULL) {

    }

    function getTrackingHtml() {
        return NULL;
    }
    
}
