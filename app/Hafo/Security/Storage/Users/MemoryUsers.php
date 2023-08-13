<?php

namespace Hafo\Security\Storage\Users;

use Hafo\Security\SecurityException;
use Hafo\Security\Storage;

final class MemoryUsers implements Storage\Users {

    private $users = [];

    function __construct($users = []) {
        $this->users = $users;
    }

    function updateLastLogin($userId, \DateTimeInterface $when) {
        if($this->exists($userId)) {
            $this->users[$userId]['last_login'] = $when;
        }
    }

    function exists($userId, $field = 'id') {
        if($field === 'id') {
            return array_key_exists($userId, $this->users);
        } else {
            foreach($this->users as $user) {
                if(array_key_exists($field, $user) && $user[$field] === $userId) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    function getUserData($userId, $select = '*') {
        if($this->exists($userId)) {
            if($select !== '*') {
                $select = is_array($select) ? $select : explode(',', $select);
                return array_filter($this->users[$userId], function($key) use ($select) {
                    return in_array($key, $select);
                }, \ARRAY_FILTER_USE_KEY);
            } else {
                return $this->users[$userId];
            }
        }

        return [];
    }

    function setUserData($userId, array $data) {
        $this->users[$userId] = array_merge(isset($this->users[$userId]) ? $this->users[$userId] : [], $data);
    }

    function register($email, array $data) {
        if($this->exists($email, 'email')) {
            throw new SecurityException('E-mail already taken.');
        }
        $id = max(array_keys($this->users)) + 1;
        $this->users[$id] = $data;
        return $id;
    }

}
