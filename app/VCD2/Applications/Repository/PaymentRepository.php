<?php

namespace VCD2\Applications\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Applications\Mapper\PaymentMapper;
use VCD2\Applications\Payment;

/**
 * @method PaymentMapper getMapper()
 *
 * @method Payment|NULL get($primaryKey)
 * @method Payment|NULL getBy(array $conds)
 *
 * @method Payment[]|ICollection find($ids)
 * @method Payment[]|ICollection findAll()
 * @method Payment[]|ICollection findBy(array $where)
 *
 * @method Payment hydrateEntity(array $data)
 */
class PaymentRepository extends Repository {

    static function getEntityClassNames() : array {
        return [Payment::class];
    }

}
