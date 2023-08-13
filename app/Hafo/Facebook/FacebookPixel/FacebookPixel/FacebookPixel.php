<?php

namespace Hafo\Facebook\FacebookPixel\FacebookPixel;

use Hafo\Facebook\FacebookPixel\FacebookPixelUserDataSource;
use Nette\Http\Session;
use Nette\Utils\Json;

class FacebookPixel implements \Hafo\Facebook\FacebookPixel\FacebookPixel {

    private $session;

    private $facebookPixelId;

    private $userDataSource;

    function __construct(Session $session, $facebookPixelId, FacebookPixelUserDataSource $userDataSource = NULL) {
        $this->session = $session->getSection('facebook.pixel');
        $this->facebookPixelId = $facebookPixelId;
        $this->userDataSource = $userDataSource;
    }

    function addPurchase(array $data) {
        $this->session['purchase'] = $data;
    }

    function getTrackingHtml() {
        return sprintf('<!-- Facebook Pixel Code --><script>%s %s %s %s</script><noscript>%s</noscript><!-- End Facebook Pixel Code -->',
            $this->getLoadFacebookJavascript(),
            $this->getInitJavascript(),
            $this->getTrackPageViewJavascript(),
            $this->getTrackPurchaseJavascript(),
            $this->getNoScriptPixelHtml()
        );
    }

    private function getLoadFacebookJavascript() {
        return '!function(f,b,e,v,n,t,s){ if(f.fbq)return;n=f.fbq=function(){ n.callMethod?
                    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
                n.push=n;n.loaded=!0;n.version=\'2.0\';n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
                    document,\'script\',\'https://connect.facebook.net/en_US/fbevents.js\');';
    }

    private function getInitJavascript() {
        $userData = $this->getUserData();
        if($userData !== NULL) {
            $userData = Json::encode($userData);
        }
        return sprintf('fbq(\'init\', \'%s\'%s);', $this->facebookPixelId, ($userData === NULL ? '' : ',' . $userData));
    }

    private function getTrackPageViewJavascript() {
        return 'fbq(\'track\', \'PageView\');';
    }

    private function getTrackPurchaseJavascript() {
        if(isset($this->session['purchase'])) {
            $purchaseData = (object)$this->session['purchase'];
            unset($this->session['purchase']);
            return sprintf('fbq(\'track\', \'Purchase\', %s);', Json::encode($purchaseData));
        }

        return '';
    }

    private function getNoScriptPixelHtml() {
        $userDataHash = $this->getUserDataHash();
        return sprintf('<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=%s&ev=PageView&noscript=1%s"/>',
            $this->facebookPixelId,
            $userDataHash === NULL ? '' : '&' . $userDataHash
        );
    }
    
    private function getUserData() {
        if($this->userDataSource === NULL) {
            return NULL;
        }

        return (object)array_filter($this->userDataSource->getUserDataForFacebookPixel());
    }

    private function getUserDataHash() {
        if($this->userDataSource === NULL) {
            return NULL;
        }

        $fbPixelUserDataHashed = [];
        foreach($this->getUserData() as $key => $val) {
            $fbPixelUserDataHashed[$key] = hash('sha256', $val);
        }
        return urldecode(http_build_query(['ud' => $fbPixelUserDataHashed]));
    }

}
