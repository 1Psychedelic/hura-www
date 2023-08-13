<?php

namespace Hafo\Security\Storage;

use Nette\Utils\Image;

/**
 * Avatars (profile pictures) storage abstraction
 */
interface Avatars {

    const SOURCE_UNKNOWN = NULL;
    const SOURCE_UPLOAD = 0;
    const SOURCE_FACEBOOK = 1;
    const SOURCE_GOOGLE = 2;

    /**
     * Check if user's avatar is old and should be updated when eg. connected to Facebook
     *
     * @param $userId
     * @param string $than
     * @return bool
     */
    function isOlderThan($userId, $than = '-1 month');

    /**
     * Checks the avatar's origin
     *
     * @param $userId
     * @param array $sources
     * @return bool
     */
    function isFromSource($userId, $sources = [self::SOURCE_UNKNOWN]);

    /**
     * Sets the user's avatar.
     *
     * @param $userId
     * @param Image $avatar
     * @param int|NULL $source
     * @return bool Success/failure
     */
    function setAvatar($userId, Image $avatar, $source = self::SOURCE_UNKNOWN);

    /**
     * Unsets the user's avatar
     *
     * @param $userId
     */
    function deleteAvatar($userId);

}
