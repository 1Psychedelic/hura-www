<?php

namespace Hafo\Security;

use Hafo\Security\Authentication\IdAuthenticator;
use Hafo\Security\Authentication\Unauthenticator;
use Hafo\Security\Storage\Users;
use Nette\Http\Session;
use Nette\Security\User;

class FakeLogin {

    private $session;

    private $user;

    private $unauthenticator;

    private $authenticator;

    private $users;

    function __construct(Session $session, User $user, Unauthenticator $unauthenticator, IdAuthenticator $authenticator, Users $users) {
        $this->session = $session->getSection(get_class());
        $this->user = $user;
        $this->unauthenticator = $unauthenticator;
        $this->authenticator = $authenticator;
        $this->users = $users;
    }

    function impersonate($userId) {
        if(!$this->user->isLoggedIn()) {
            return;
        }
        if(!$this->isActive()) {
            $this->session['originalUser'] = $this->user->id;
        }
        $this->unauthenticator->logout();
        $this->authenticator->login($userId);
    }

    function disable() {
        if(!$this->isActive()) {
            return;
        }
        $userId = $this->session['originalUser'];
        unset($this->session['originalUser']);
        $this->unauthenticator->logout();
        $this->authenticator->login($userId);
    }

    function isActive() {
        return isset($this->session['originalUser']);
    }

    function getOriginalUser() {
        if($this->isActive()) {
            return $this->users->getUserData($this->session['originalUser']);
        }
        return NULL;
    }

}
