<?php

namespace VCD2\Utils\Temporary;

use VCD2\Orm;

class UpdateVipStatus {

    private $orm;

    function __construct(Orm $orm) {
        $this->orm = $orm;
    }

    function updateVipStatus() {
        $users = $this->orm->users->findAll();
        foreach ($users as $user) {
            $user->updateVipStatus();
            $this->orm->persistAndFlush($user);
        }
    }

}
