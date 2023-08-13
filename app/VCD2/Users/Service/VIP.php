<?php

namespace VCD2\Users\Service;

use VCD2\Orm;
use VCD2\Users\User;

/** @deprecated one time use */
class VIP {

    private $orm;

    function __construct(Orm $orm) {
        $this->orm = $orm;
    }

    /**
     * @return User[]
     */
    function findVipUsers() {
        $vips = [];
        $users = $this->orm->users->findAll();
        foreach($users as $user) {
            if($user->isVip) {
                $vips[$user->id] = $user;
            }
        }
        return $vips;
    }
    
    function findVipUsersArray() {
        $data = [];
        $vips = $this->findVipUsers();
        foreach($vips as $vip) {
            $data[$vip->id] = $vip->email . ' - ' . $vip->name;
        }
        return $data;
    }

    function findVipUsersIds() {
        return array_keys($this->findVipUsersArray());
    }

}
