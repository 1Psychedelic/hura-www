<?php

namespace Hafo\Facebook\UI;

use Hafo\Facebook\FacebookLogin;
use Hafo\Security\JsSDK;
use Hafo\Security\SecurityException;
use Nette\Application\UI\Component;

/**
 * @method onAuthorize()
 * @method onDeauthorize()
 * @method onError(SecurityException $e)
 */
final class FacebookLoginComponent extends Component {

    /**
     * @var array of function()
     */
    public $onAuthorize = [];

    /**
     * @var array of function()
     */
    public $onDeauthorize = [];

    /**
     * @var array of function(SecurityException $e)
     */
    public $onError = [];

    private $facebookLogin;

    function __construct(FacebookLogin $facebookLogin) {
        $this->facebookLogin = $facebookLogin;
    }

    function handleAuthorize() {
        try {
            $this->facebookLogin->login($this->presenter->request->getPost('signedRequest'));
        } catch (SecurityException $e) {
            $this->onError($e);
        }
        $this->presenter->payload->user = (new JsSDK($this->presenter->user))->payload();
        $this->onAuthorize();
    }

    function handleDeauthorize() {
        $this->presenter->payload->user = (new JsSDK($this->presenter->user))->payload();
        $this->onDeauthorize();
    }

}
