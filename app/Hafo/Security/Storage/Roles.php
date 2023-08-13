<?php

namespace Hafo\Security\Storage;

/**
 * Roles storage abstraction
 */
interface Roles {

    /**
     * Gets user roles
     *
     * @param int $userId
     * @return string[]
     */
    function getUserRoles($userId);

    /**
     * Sets user roles
     *
     * @param int $userId
     * @param string[] $roles
     */
    function setUserRoles($userId, array $roles);

    /**
     * Returns IDs of users with specified role.
     *
     * @param string $role
     * @return int[]
     */
    function getUserIds($role);

}
