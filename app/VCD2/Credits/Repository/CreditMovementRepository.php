<?php

namespace VCD2\Credits\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Credits\CreditMovement;
use VCD2\Credits\Mapper\CreditMovementMapper;

/**
 * @method CreditMovementMapper getMapper()
 *
 * @method CreditMovement|NULL get($primaryKey)
 * @method CreditMovement|NULL getBy(array $conds)
 *
 * @method CreditMovement[]|ICollection find($ids)
 * @method CreditMovement[]|ICollection findAll()
 * @method CreditMovement[]|ICollection findBy(array $where)
 *
 * @method CreditMovement hydrateEntity(array $data)
*/
class CreditMovementRepository extends Repository {

    static function getEntityClassNames() : array {
        return [CreditMovement::class];
    }

}
