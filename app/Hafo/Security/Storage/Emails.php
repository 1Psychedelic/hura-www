<?php

namespace Hafo\Security\Storage;

use Hafo\Security\SecurityException;

/**
 * A service for users' e-mail verification
 */
interface Emails {

    /**
     * Checks whether a given e-mail was verified
     *
     * @param string $email
     * @return bool
     */
    function isVerified($email);

    /**
     * Verify an e-mail by hash
     *
     * @param string $email
     * @param string $hash
     * @throws SecurityException
     */
    function verify($email, $hash);

    /**
     * Request an e-mail verification hash
     *
     * @param string $email
     * @return string
     * @throws SecurityException
     */
    function requestEmailVerifyHash($email);

}
