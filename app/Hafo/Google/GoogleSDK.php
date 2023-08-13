<?php

namespace Hafo\Google;

use Nette\Utils\IHtmlString;

final class GoogleSDK implements IHtmlString {

    private $appId;

    function __construct($appId) {
        $this->appId = $appId;
    }

    function __toString() {
        return '<script src="https://apis.google.com/js/platform.js?onload=googleLoginLoaded" async defer></script><script type="text/javascript">Hafo.Google.clientId = "' . $this->appId . '";</script>';
    }

    static function buttonOnclick($authorizeUrl) {
        return 'event.stopPropagation();Hafo.Google.button = this;Hafo.Google.login(\'' . $authorizeUrl . '\');';
    }

}
