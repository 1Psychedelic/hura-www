<?php

namespace Hafo\Security\Storage\Avatars;

use Hafo\FileStorage\UrlAccessibleStorage;
use Hafo\Security\Storage;
use Nette\Utils\Image;
use Nette\Utils\Random;
use Nette\Database\Context;

final class DatabaseAvatars implements Storage\Avatars {

    private $database;

    private $storage;

    function __construct(Context $database, UrlAccessibleStorage $storage) {
        $this->database = $database;
        $this->storage = $storage;
    }

    function isOlderThan($userId, $olderThan = '-1 month') {
        $user = $this->database->table('system_user')->wherePrimary($userId)->fetch();
        if(!$user) {
            return FALSE;
        }
        return $user['avatar_updated'] === NULL || !$user['avatar_updated']->diff((new \DateTime)->modify($olderThan))->invert;
    }

    function isFromSource($userId, $sources = [self::SOURCE_UNKNOWN]) {
        $user = $this->database->table('system_user')->wherePrimary($userId)->fetch();
        if(!$user) {
            return FALSE;
        }
        return in_array($user['avatar_source'], $sources);
    }

    function setAvatar($userId, Image $avatar, $source = self::SOURCE_UNKNOWN) {
        $user = $this->database->table('system_user')->wherePrimary($userId)->fetch();
        if(!$user) {
            return false;
        }
        $large = $this->storage->dir($userId)->write(Random::generate(3) . '-' . $userId . '.jpg', $avatar->toString());
        $small = $this->storage->dir($userId)->write(Random::generate(3) . '-s' . $userId . '.jpg', $avatar->resize(NULL, 100)->toString());
        return (bool)$this->database->table('system_user')->wherePrimary($userId)->update([
            'avatar_small' => $this->storage->pathToUrl($small),
            'avatar_large' => $this->storage->pathToUrl($large),
            'avatar_updated' => new \DateTime,
            'avatar_source' => $source
        ]);
    }

    function deleteAvatar($userId) {
        $user = $this->database->table('system_user')->wherePrimary($userId)->fetch();
        if(!$user) {
            return;
        }
        $this->database->table('system_user')->wherePrimary($userId)->update([
            'avatar_small' => NULL,
            'avatar_large' => NULL,
            'avatar_updated' => new \DateTime,
            'avatar_source' => self::SOURCE_UPLOAD
        ]);
    }

}
