<?php

namespace VCD2\Events\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Events\EventTab;
use VCD2\Events\Mapper\EventTabMapper;

/**
 * @method EventTabMapper getMapper()
 *
 * @method EventTab|NULL get($primaryKey)
 * @method EventTab|NULL getBy(array $conds)
 *
 * @method EventTab[]|ICollection find($ids)
 * @method EventTab[]|ICollection findAll()
 * @method EventTab[]|ICollection findBy(array $where)
 *
 * @method EventTab hydrateEntity(array $data)
 *
 */
class EventTabRepository extends Repository {

    static function getEntityClassNames() : array {
        return [EventTab::class];
    }

}
