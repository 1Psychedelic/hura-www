<?php

namespace Hafo\Security;

use Nette\Security\User;
use Nette\Utils\IHtmlString;

class JsSDK implements IHtmlString {

    private $user;

    function __construct(User $user) {
        $this->user = $user;
    }

    function payload() {
        return (object)[
            'loggedIn' => $this->user->isLoggedIn(),
            'id' => $this->user->getId(),
            'roles' => array_map(
                function ($value) {
                    return (string)$value;
                }, $this->user->getRoles()
            ),
            'data' => $this->user->getIdentity() === NULL ? (object)[] : $this->user->getIdentity()->data
        ];
    }

    function __toString() {
        return '<script type="text/javascript">$(document).ready(function(){Hafo.User.setCurrentUser(' . json_encode($this->payload()) . ');});</script>';
    }

}
