<?php

namespace Hafo\Security\Storage;
use Hafo\Security\SecurityException;

/**
 * User storage abstraction
 */
interface Users {

    /**
     * Updates a last login value
     *
     * @param int $userId
     * @param \DateTimeInterface $when
     */
    function updateLastLogin($userId, \DateTimeInterface $when);

    /**
     * Checks whether a user exists
     *
     * @param int $userId
     * @param string $field Use different field (common: email, google_id, facebook_id, steam_id)
     * @return int|bool ID of the user or FALSE if not found
     */
    function exists($userId, $field = 'id');

    /**
     * Gets user data
     *
     * @param int $userId
     * @param string|array $select '*' or fields as array or comma-separated fields
     * @return array
     */
    function getUserData($userId, $select = '*');

    /**
     * Updates user data
     *
     * @param int $userId
     * @param array $data
     */
    function setUserData($userId, array $data);

    /**
     * Registers a user
     *
     * @param string $email
     * @param array $data
     * @return int User's ID
     * @throws SecurityException If user with such e-mail already exists
     */
    function register($email, array $data);

}
