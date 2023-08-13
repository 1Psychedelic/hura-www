<?php

namespace VCD2\Applications\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Applications\Child;
use VCD2\Applications\Mapper\ChildMapper;

/**
 * @method ChildMapper getMapper()
 *
 * @method Child|NULL get($primaryKey)
 * @method Child|NULL getBy(array $conds)
 *
 * @method Child[]|ICollection find($ids)
 * @method Child[]|ICollection findAll()
 * @method Child[]|ICollection findBy(array $where)
 *
 * @method Child hydrateEntity(array $data)
 */
class ChildRepository extends Repository {

    static function getEntityClassNames() : array {
        return [Child::class];
    }

}
