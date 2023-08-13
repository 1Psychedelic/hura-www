<?php

namespace VCD2\Users\Service;

use Hafo\Security\Authentication\EmailAlreadyVerifiedException;
use Hafo\Security\SecurityException;
use Nette\Utils\Random;
use VCD2\Orm;

class Emails implements \Hafo\Security\Storage\Emails {

    private $orm;

    function __construct(Orm $orm) {
        $this->orm = $orm;
    }

    function isVerified($email) {
        $user = $this->orm->users->getByEmail($email);
        if($user === NULL) {
            return FALSE;
        }

        return $user->emailVerified;
    }

    function verify($email, $hash) {
        if($this->isVerified($email)) {
            throw new EmailAlreadyVerifiedException;
        }

        $user = $this->orm->users->getByEmail($email);
        if($user === NULL || $user->emailVerifyHash === NULL || $user->emailVerifyHash !== $hash) {
            throw new SecurityException('Invalid e-mail/hash combination.');
        }

        $user->emailVerified = TRUE;
        $user->emailVerifyHash = NULL;

        $this->orm->persistAndFlush($user);
    }

    function requestEmailVerifyHash($email) {
        if($this->isVerified($email)) {
            throw new EmailAlreadyVerifiedException;
        }

        $user = $this->orm->users->getByEmail($email);
        if($user === NULL) {
            throw new SecurityException('User not found.');
        }

        $hash = $user->emailVerifyHash;
        if($hash === NULL) {
            $hash = Random::generate(40);
            $user->emailVerifyHash = $hash;
            $this->orm->persistAndFlush($user);
        }

        return $hash;
    }

}
