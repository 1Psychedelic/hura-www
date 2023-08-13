<?php

namespace Hafo\Security\Authentication;

/**
 * A service for logging out
 */
interface Unauthenticator {
    
    /**
     * Logout a current user
     */
    function logout();

    /**
     * Logout a current user on all devices but current
     */
    function remoteLogout();
    
}
