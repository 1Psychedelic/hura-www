<?php

namespace Hafo\Facebook;

use Nette\Utils\IHtmlString;

final class FacebookSDK implements IHtmlString {

    private $appId;

    private $version;

    private $locale;

    private $init = [];

    function __construct($appId, $version = '2.5', $locale = 'cs_CZ') {
        $this->appId = $appId;
        $this->version = $version;
        $this->locale = $locale;
    }

    function addInit($jsCode) {
        $this->init[] = (string)$jsCode;
        return $this;
    }

    function __toString() {
        return '<script type="text/javascript">window.fbAsyncInit=function(){FB.init({appId:\''
        . $this->appId . '\',xfbml:true,version:\'v'
        . $this->version . '\'});'
        . implode(';', $this->init)
        . '};(function(d, s, id){var js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id)){return;}js=d.createElement(s);js.id=id;js.src="//connect.facebook.net/' . $this->locale . '/sdk.js";fjs.parentNode.insertBefore(js,fjs);}(document,\'script\',\'facebook - jssdk\'));</script>';
    }

    static function buttonOnclick($authorizeUrl, $deauthorizeUrl) {
        return 'event.stopPropagation();Hafo.Facebook.button = this;Hafo.Facebook.init(\'' . $authorizeUrl . '\', \'' . $deauthorizeUrl . '\');FB.login(function(response){ }, { scope: \'email\'})';
    }

}
