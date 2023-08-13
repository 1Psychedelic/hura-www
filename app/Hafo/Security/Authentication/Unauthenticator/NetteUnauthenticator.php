<?php

namespace Hafo\Security\Authentication\Unauthenticator;

use Hafo\Security\Authentication;
use Hafo\Security\Storage;
use Nette\Security\User;

final class NetteUnauthenticator implements Authentication\Unauthenticator {

    private $loginTokens;

    private $authenticator;

    private $user;

    function __construct(Storage\LoginTokens $loginTokens, Authentication\IdAuthenticator $authenticator, User $user) {
        $this->loginTokens = $loginTokens;
        $this->authenticator = $authenticator;
        $this->user = $user;
    }

    function logout() {
        $this->user->logout(TRUE);
    }

    function remoteLogout() {
        if($this->user->isLoggedIn()) {
            $this->loginTokens->refreshLoginToken($this->user->id);
            $this->authenticator->login($this->user->id);
        }
    }

}
