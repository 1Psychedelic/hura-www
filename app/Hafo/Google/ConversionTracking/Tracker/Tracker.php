<?php

namespace Hafo\Google\ConversionTracking\Tracker;

use Hafo\Google\ConversionTracking\Conversion;
use Nette\Http\Session;

class Tracker implements \Hafo\Google\ConversionTracking\Tracker {

    private $session;

    private $adwordsId;

    function __construct(Session $session, $adwordsId) {
        $this->session = $session->getSection('google.conversionTracking');
        $this->adwordsId = $adwordsId;
    }

    function addConversion($name, $conversionId, $transactionId = NULL) {
        $this->session[$conversionId] = serialize(new Conversion($name, $this->adwordsId, $conversionId, $transactionId));
    }

    function getTrackingHtml() {
        $html = '';
        foreach($this->session as $key => $conversion) {
            $conversionObj = unserialize($conversion);
            $html .= "\n\n" . $conversionObj->getHtml();
            unset($this->session[$key]);
        }
        return $html;
    }

}
