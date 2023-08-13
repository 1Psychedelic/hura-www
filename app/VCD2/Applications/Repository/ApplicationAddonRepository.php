<?php

namespace VCD2\Applications\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Applications\ApplicationAddon;
use VCD2\Applications\Mapper\ApplicationAddonMapper;

/**
 * @method ApplicationAddonMapper getMapper()
 *
 * @method ApplicationAddon|NULL get($primaryKey)
 * @method ApplicationAddon|NULL getBy(array $conds)
 *
 * @method ApplicationAddon[]|ICollection find($ids)
 * @method ApplicationAddon[]|ICollection findAll()
 * @method ApplicationAddon[]|ICollection findBy(array $where)
 *
 * @method ApplicationAddon hydrateEntity(array $data)
 */
class ApplicationAddonRepository extends Repository {

    static function getEntityClassNames() : array {
        return [ApplicationAddon::class];
    }

}
