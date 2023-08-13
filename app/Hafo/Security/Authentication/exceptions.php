<?php

namespace Hafo\Security\Authentication;

use Hafo\Security\SecurityException;

class LoginException extends SecurityException {}

class EmailNotVerifiedException extends LoginException {

    private $userId;

    function setUserId($userId) {
        $this->userId = $userId;
        return $this;
    }

    function getUserId() {
        return $this->userId;
    }

}

class EmailAlreadyVerifiedException extends SecurityException {}
