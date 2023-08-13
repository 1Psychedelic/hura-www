<?php

namespace Hafo\Google\ConversionTracking;

class Conversion {

    public $name;

    public $adwordsId;

    public $conversionId;

    public $transactionId;

    function __construct($name, $adwordsId, $conversionId, $transactionId = NULL) {
        $this->name = $name;
        $this->adwordsId = $adwordsId;
        $this->conversionId = $conversionId;
        $this->transactionId = $transactionId;
    }

    function getHtml() {
        return sprintf("<!-- Event snippet for %s conversion page --> <script> gtag('event', 'conversion', {'send_to': 'AW-%s/%s'%s}); </script>",
            $this->name,
            $this->adwordsId,
            $this->conversionId,
            ($this->transactionId === NULL ? '' : ", 'transaction_id': " . $this->transactionId)
        );
    }

}
