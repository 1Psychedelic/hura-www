<?php

namespace Hafo\Security;

use Hafo\Security\Authentication\Unauthenticator;
use Hafo\Security\Storage\LoginTokens;
use Nette\SmartObject;

/**
 * @method onRemoteLogout()
 */
final class Security {

    use SmartObject;

    /**
     * @var array of function()
     */
    public $onRemoteLogout = [];

    private $loginTokens;

    private $unauthenticator;

    function __construct(LoginTokens $loginTokens, Unauthenticator $unauthenticator) {
        $this->loginTokens = $loginTokens;
        $this->unauthenticator = $unauthenticator;
    }

    /**
     * Logouts a current user if his login token isn't valid
     *
     * @param int $userId
     * @param string|NULL $token
     */
    function enforceLoginValid($userId, $token = NULL) {
        if(!$this->loginTokens->isLoginTokenValid($userId, $token)) {
            $this->unauthenticator->logout();
            $this->onRemoteLogout();
        }
    }

}
