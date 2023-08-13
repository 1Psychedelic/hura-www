<?php

namespace Hafo\Security\Authentication;

/**
 * A generic authenticator
 */
interface Authenticator {

    /**
     * Login a user by any means necessary
     *
     * @param mixed $credentials
     * @throws LoginException
     */
    function login($credentials);
    
}
