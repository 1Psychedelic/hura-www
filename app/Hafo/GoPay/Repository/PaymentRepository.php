<?php

namespace Hafo\GoPay\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use Hafo\GoPay\Mapper\PaymentMapper;
use Hafo\GoPay\Payment;

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
