<?php

namespace Hafo\Google\ConversionTracking;

interface Tracker {

    function addConversion($name, $conversionId, $transactionId = NULL);

    function getTrackingHtml();

}
