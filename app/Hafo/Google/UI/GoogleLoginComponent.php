<?php

namespace Hafo\Google\UI;

use Hafo\Google\GoogleLogin;
use Hafo\Security\JsSDK;
use Hafo\Security\SecurityException;
use Nette\Application\UI\Component;

/**
 * @method onAuthorize()
 * @method onDeauthorize()
 * @method onError(SecurityException $e)
 */
final class GoogleLoginComponent extends Component {

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

    private $login;

    function __construct(GoogleLogin $login) {
        $this->login = $login;
    }

    function handleAuthorize() {
        try {
            $this->login->login($this->presenter->request->getPost('token'));
        } catch (SecurityException $e) {
            $this->onError($e);
            return;
        }
        $this->presenter->payload->user = (new JsSDK($this->presenter->user))->payload();
        $this->onAuthorize();
    }

    function handleDeauthorize() {
        $this->presenter->payload->user = (new JsSDK($this->presenter->user))->payload();
        $this->onDeauthorize();
    }

}
