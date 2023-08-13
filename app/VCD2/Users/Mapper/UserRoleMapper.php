<?php

namespace VCD2\Users\Mapper;

use Hafo\Orm\Mapper\Mapper;

class UserRoleMapper extends Mapper {

    public function getTableName() : string {
        return 'system_user_role';
    }

}
