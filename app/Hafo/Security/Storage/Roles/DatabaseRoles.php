<?php

namespace Hafo\Security\Storage\Roles;

use Hafo\Security\Storage;
use Nette\Database\Context;

final class DatabaseRoles implements Storage\Roles {

    private $db;

    function __construct(Context $db) {
        $this->db = $db;
    }

    function getUserRoles($userId) {
        return $this->db->table('system_user_role')->where('user', $userId)->fetchPairs(NULL, 'role');
    }

    function setUserRoles($userId, array $roles) {
        $this->db->beginTransaction();
        $this->db->table('system_user_role')->where('user', $userId)->delete();
        foreach($roles as $role) {
            $this->db->table('system_user_role')->insert([
                'user' => $userId,
                'role' => $role
            ]);
        }
        $this->db->commit();
    }

    function getUserIds($role) {
        return $this->db->table('system_user_role')->where('role', $role)->fetchPairs(NULL, 'user');
    }

}
