<?php

namespace VCD\Notifications\DefaultModel;

use Nette\Caching\Cache;
use Nette\Security\User;
use VCD\Notifications as VN;

class CachedNotifications implements VN\Notifications {

    const CACHE_TAG = 'vcd.notifications';

    private $notifications;

    private $user;

    private $cache;

    function __construct(VN\Notifications $notifications, User $user, Cache $cache) {
        $this->notifications = $notifications;
        $this->user = $user;
        $this->cache = $cache;
    }

    function add($message, $user = NULL, $related = NULL, $type = self::TYPE_MESSAGE, $push = false) {
        $this->notifications->add($message, $user, $related, $type, $push);
        $this->cache->clean([Cache::TAGS => [self::CACHE_TAG]]);
    }

    function load() {
        return $this->notifications->load();
    }

    function count() {
        return $this->cache->load(['count', $this->user->id], function(&$dependencies) {
            $this->addDependencies($dependencies);
            return $this->notifications->count();
        });
    }

    private function addDependencies(&$dependencies) {
        $dependencies[Cache::EXPIRE] = (new \DateTime)->modify('+1 day')->setTime(0, 0, 0);
        $dependencies[Cache::TAGS] = [self::CACHE_TAG];
    }

}
