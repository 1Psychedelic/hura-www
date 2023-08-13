<?php

namespace Hafo\Google\ReCaptcha\ReCaptchaV3;

class NoReCaptchaV3 implements \Hafo\Google\ReCaptcha\ReCaptchaV3 {

    function getInitLibraryHtml() {
        return '';
    }

    function getInitFormHtml($action, $fieldClassName) {
        return '';
    }

    function verify($token) {
        return TRUE;
    }

}
