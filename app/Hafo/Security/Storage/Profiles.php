<?php

namespace Hafo\Security\Storage;

/**
 * Users' profile info storage abstraction
 */
interface Profiles {

    /**
     * Returns a user's profile info
     *
     * @param int $userId
     * @return array
     */
    function load($userId);

    /**
     * Save a user's profile info
     *
     * @param int $userId
     * @param array $data
     * @return mixed
     */
    function save($userId, array $data);

}
