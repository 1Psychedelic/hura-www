<?php

namespace VCD2\Users\Service;

use Nette\Security\User;
use VCD2\Orm;

class UserContext {

    private $orm;

    private $user;

    function __construct(Orm $orm, User $user) {
        $this->orm = $orm;
        $this->user = $user;
    }

    function getEntity() {
        return $this->user->isLoggedIn() ? $this->orm->users->get($this->user->id) : NULL;
    }

}
