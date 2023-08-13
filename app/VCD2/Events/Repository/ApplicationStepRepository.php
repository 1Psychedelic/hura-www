<?php

namespace VCD2\Events\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Events\ApplicationStep;
use VCD2\Events\Mapper\ApplicationStepMapper;

/**
* @method ApplicationStepMapper getMapper()
*
* @method ApplicationStep|NULL get($primaryKey)
* @method ApplicationStep|NULL getBy(array $conds)
*
* @method ApplicationStep[]|ICollection find($ids)
* @method ApplicationStep[]|ICollection findAll()
* @method ApplicationStep[]|ICollection findBy(array $where)
*
* @method ApplicationStep hydrateEntity(array $data)
*/
class ApplicationStepRepository extends Repository {

    static function getEntityClassNames() : array {
        return [ApplicationStep::class];
    }

}
