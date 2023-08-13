<?php

namespace VCD2\Events\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Events\EventAddon;
use VCD2\Events\Mapper\EventAddonMapper;

/**
 * @method EventAddonMapper getMapper()
 *
 * @method EventAddon|NULL get($primaryKey)
 * @method EventAddon|NULL getBy(array $conds)
 *
 * @method EventAddon[]|ICollection find($ids)
 * @method EventAddon[]|ICollection findAll()
 * @method EventAddon[]|ICollection findBy(array $where)
 *
 * @method EventAddon hydrateEntity(array $data)
 *
 */
class EventAddonRepository extends Repository {

    static function getEntityClassNames() : array {
        return [EventAddon::class];
    }

}
