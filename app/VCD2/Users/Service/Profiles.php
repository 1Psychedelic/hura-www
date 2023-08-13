<?php

namespace VCD2\Users\Service;

use VCD2\Orm;

class Profiles implements \Hafo\Security\Storage\Profiles {

    private $orm;

    function __construct(Orm $orm) {
        $this->orm = $orm;
    }

    function load($userId) {
        $user = $this->orm->users->get($userId);
        if($user) {
            return $user->getValues();
        }
        return [];
    }

    function save($userId, array $data) {
        $user = $this->orm->users->get($userId);
        if($user) {
            $user->setValues($data);
            $this->orm->persistAndFlush($user);
        }
    }

}
