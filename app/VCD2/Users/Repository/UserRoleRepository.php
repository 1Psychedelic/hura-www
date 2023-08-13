<?php

namespace VCD2\Users\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Users\Mapper\UserRoleMapper;
use VCD2\Users\UserRole;

/**
* @method UserRoleMapper getMapper()
*
* @method UserRole|NULL get($primaryKey)
* @method UserRole|NULL getBy(array $conds)
*
* @method UserRole[]|ICollection find($ids)
* @method UserRole[]|ICollection findAll()
* @method UserRole[]|ICollection findBy(array $where)
*
* @method UserRole hydrateEntity(array $data)
*/
class UserRoleRepository extends Repository {

    static function getEntityClassNames() : array {
        return [UserRole::class];
    }

}
