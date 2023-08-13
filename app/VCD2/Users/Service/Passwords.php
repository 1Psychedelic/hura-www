<?php

namespace VCD2\Users\Service;

use Nette\Security\Passwords as BCrypt;
use Nette\Utils\Random;
use VCD2\Orm;

class Passwords implements \Hafo\Security\Storage\Passwords {

    private $orm;

    function __construct(Orm $orm) {
        $this->orm = $orm;
    }

    function requestPasswordResetHash($userId) {
        $hash = Random::generate(40);
        $user = $this->orm->users->get($userId);
        $user->passwordRestore = $hash;
        $this->orm->persistAndFlush($user);
        return $hash;
    }

    function isPasswordResetHashValid($hash) {
        if(!$hash) {
            return FALSE;
        }
        $user = $this->orm->users->getBy(['passwordRestore' => $hash]);

        return $user === null ? false : $user->id;
    }

    function setPassword($userId, $passwordPlain) {
        $user = $this->orm->users->get($userId);
        $user->password = BCrypt::hash($passwordPlain);
        $user->passwordRestore = NULL;
        $this->orm->persistAndFlush($user);
    }

    function verifyPassword($email, $passwordPlain) {
        $user = $this->orm->users->getByEmail($email);

        if($user === NULL || strlen($user->password) === 0) {
            return FALSE;
        }

        if(BCrypt::verify($passwordPlain, $user->password)) {
            return $user->id;
        }
        
        return FALSE;
    }

}
