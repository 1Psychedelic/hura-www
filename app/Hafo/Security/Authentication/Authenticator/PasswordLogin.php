<?php

namespace Hafo\Security\Authentication\Authenticator;

use Hafo\Security\Authentication;
use Hafo\Security\Storage;

final class PasswordLogin implements Authentication\Authenticator {

    private $passwords;

    private $emails;

    private $users;

    private $authenticator;

    function __construct(Storage\Passwords $passwords, Storage\Emails $emails, Storage\Users $users, Authentication\IdAuthenticator $authenticator) {
        $this->passwords = $passwords;
        $this->emails = $emails;
        $this->users = $users;
        $this->authenticator = $authenticator;
    }

    function login($credentials) {
        if(!is_array($credentials) || !isset($credentials['email']) || !isset($credentials['password'])) {
            throw new Authentication\LoginException;
        }
        if(!$this->emails->isVerified($credentials['email'])) {
            $e = new Authentication\EmailNotVerifiedException;
            if($id = $this->users->exists($credentials['email'], 'email')) {
                $e->setUserId($id);
            }
            throw $e;
        }
        if(!$id = $this->passwords->verifyPassword($credentials['email'], $credentials['password'])) {
            throw new Authentication\LoginException;
        }
        $this->authenticator->login($id);
    }

}
