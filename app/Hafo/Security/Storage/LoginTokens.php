<?php

namespace Hafo\Security\Storage;

/**
 * A service for managing login tokens for increased security and remote logout functionality
 */
interface LoginTokens {

    /**
     * Checks whether a given user has a login token set.
     *
     * @param int $userId
     * @return bool
     */
    function hasLoginToken($userId);

    /**
     * Checks whether a given token is valid.
     *
     * @param int $userId
     * @param string $token
     * @return bool
     */
    function isLoginTokenValid($userId, $token);

    /**
     * Generates a new login token, effectively logging user out
     *
     * @param int $userId
     */
    function refreshLoginToken($userId);

}
