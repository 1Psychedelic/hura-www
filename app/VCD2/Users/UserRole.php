<?php

namespace VCD2\Users;

use Hafo\Orm\Entity\Entity;

/**
 * @property mixed $id {primary-proxy}
 * @property User $user {m:1 User::$roles} {primary}
 * @property string $role {enum self::ROLE_*} {primary}
 */
class UserRole extends Entity {

    const ROLE_ADMIN = 'admin';
    const ROLE_NOTIFY = 'notify';

    function __construct(User $user, $role) {
        parent::__construct();
        $this->user = $user;
        $this->role = $role;
    }

}
