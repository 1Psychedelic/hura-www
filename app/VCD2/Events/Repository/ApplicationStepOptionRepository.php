<?php

namespace VCD2\Events\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Events\ApplicationStepOption;
use VCD2\Events\Mapper\ApplicationStepOptionMapper;

/**
* @method ApplicationStepOptionMapper getMapper()
*
* @method ApplicationStepOption|NULL get($primaryKey)
* @method ApplicationStepOption|NULL getBy(array $conds)
*
* @method ApplicationStepOption[]|ICollection find($ids)
* @method ApplicationStepOption[]|ICollection findAll()
* @method ApplicationStepOption[]|ICollection findBy(array $where)
*
* @method ApplicationStepOption hydrateEntity(array $data)
*/
class ApplicationStepOptionRepository extends Repository {

    static function getEntityClassNames() : array {
        return [ApplicationStepOption::class];
    }

}
