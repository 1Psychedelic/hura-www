<?php

namespace Hafo\Security\Storage;

/**
 * A service for managing users' passwords
 */
interface Passwords {

    /**
     * Requests a password reset hash
     *
     * @param int $userId ID
     * @return string Password reset hash
     */
    function requestPasswordResetHash($userId);

    /**
     * Checks whether a given password reset hash is valid
     *
     * @param string $hash
     * @return int|bool ID of the user this hash belongs to or FALSE if not found
     */
    function isPasswordResetHashValid($hash);

    /**
     * Sets a new password
     *
     * @param int $userId
     * @param string $passwordPlain
     */
    function setPassword($userId, $passwordPlain);

    /**
     * Checks whether a given password is valid
     *
     * @param string $email
     * @param string $passwordPlain
     * @return int|bool ID of user or FALSE if login is incorrect
     */
    function verifyPassword($email, $passwordPlain);

}
