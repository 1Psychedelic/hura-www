<?php

namespace Hafo\Security\Authentication;

/**
 * A service for finally logging a user by his ID once we've performed other checks
 */
interface IdAuthenticator {

    /**
     * Login a user by ID
     *
     * @param int $userId
     * @throws LoginException
     */
    function login($userId);

}
