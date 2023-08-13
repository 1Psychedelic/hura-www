<?php

namespace VCD2\Applications\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Applications\Mapper\StepChoiceMapper;
use VCD2\Applications\StepChoice;

/**
* @method StepChoiceMapper getMapper()
*
* @method StepChoice|NULL get($primaryKey)
* @method StepChoice|NULL getBy(array $conds)
*
* @method StepChoice[]|ICollection find($ids)
* @method StepChoice[]|ICollection findAll()
* @method StepChoice[]|ICollection findBy(array $where)
*
* @method StepChoice hydrateEntity(array $data)
*/
class StepChoiceRepository extends Repository {

    static function getEntityClassNames() : array {
        return [StepChoice::class];
    }

}
