<?php

namespace VCD2\Events\Repository;

use Hafo\Http\CacheHeaders;
use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Events\Event;
use VCD2\Events\Mapper\EventMapper;

/**
 * @method EventMapper getMapper()
 *
 * @method Event|NULL get($primaryKey)
 * @method Event|NULL getBy(array $conds)
 *
 * @method Event[]|ICollection find($ids)
 * @method Event[]|ICollection findAll()
 * @method Event[]|ICollection findBy(array $where)
 *
 * @method Event hydrateEntity(array $data)
 *
 * @method Event[]|ICollection findUpcoming($type = NULL, $includeHidden = FALSE, $strict = FALSE)
 * @method Event[]|ICollection findArchived()
 * @method Event[]|ICollection findArchivedRandom()
 * @method int countUpcoming($type = NULL, $includeHidden = FALSE)
 *
 * @method string[] findSelectOptionsForAdmin()
 *
 * @method Event|NULL getCurrentEvent()
 *
 * @method CacheHeaders|null getUpcomingCacheHeaders()
 */
class EventRepository extends Repository {

    static function getEntityClassNames() : array {
        return [Event::class];
    }

}
