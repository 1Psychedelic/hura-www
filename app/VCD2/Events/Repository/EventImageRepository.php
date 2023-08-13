<?php

namespace VCD2\Events\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Events\EventImage;
use VCD2\Events\Mapper\EventImageMapper;

/**
 * @method EventImageMapper getMapper()
 *
 * @method EventImage|NULL get($primaryKey)
 * @method EventImage|NULL getBy(array $conds)
 *
 * @method EventImage[]|ICollection find($ids)
 * @method EventImage[]|ICollection findAll()
 * @method EventImage[]|ICollection findBy(array $where)
 *
 * @method EventImage hydrateEntity(array $data)
 *
 */
class EventImageRepository extends Repository {

    static function getEntityClassNames() : array {
        return [EventImage::class];
    }

}
