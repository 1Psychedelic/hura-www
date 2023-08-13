<?php

namespace Hafo\Security\Authentication\IdAuthenticator;

use Hafo\Security\Authentication;
use Hafo\Security\Storage;
use Nette\Application\Application;
use Nette\Security\User;

final class NetteAuthenticator implements Authentication\IdAuthenticator {

    private $users;

    private $loginTokens;

    private $roles;

    private $user;

    private $identityFactory;
    
    function __construct(Storage\Users $users, Storage\LoginTokens $loginTokens, Storage\Roles $roles, User $user, NetteIdentityFactory $identityFactory, Application $application = NULL) {
        $this->users = $users;
        $this->loginTokens = $loginTokens;
        $this->roles = $roles;
        $this->user = $user;
        $this->identityFactory = $identityFactory;
        if($application !== NULL) {
            $application->onRequest[] = function() {
                $this->guard();
            };
        }
    }
    
    function login($userId) {
        if(empty($userId)) {
            throw new Authentication\LoginException;
        }
        $userId = intval($userId);
        if($this->users->exists($userId)) {
            if(!$this->loginTokens->hasLoginToken($userId)) {
                $this->loginTokens->refreshLoginToken($userId);
            }
            $this->users->updateLastLogin($userId, new \DateTime);
            $this->user->login($this->createIdentity($userId));
        } else {
            throw new Authentication\LoginException;
        }
    }

    function guard() {
        if($this->user->isLoggedIn()) {
            $token = isset($this->user->identity->data['login_token']) ? $this->user->identity->data['login_token'] : NULL;
            if(!$this->loginTokens->isLoginTokenValid($this->user->id, $token)) {
                $this->user->logout(TRUE);
            }
        }
    }

    private function createIdentity($id) {
        return $this->identityFactory->create($id, $this->roles->getUserRoles($id), $this->users->getUserData($id));
    }

}
