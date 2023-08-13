<?php

namespace Hafo\Google\ReCaptcha;

interface ReCaptchaV3
{

    /** @return string */
    function getInitLibraryHtml();

    /**
     * @param string $action
     * @param string $fieldClassName
     * @return string
     */
    function getInitFormHtml($action, $fieldClassName);

    /**
     * @param string $token
     * @return bool
     */
    function verify($token);

}
