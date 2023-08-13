<?php

namespace Hafo\Fio\Repository;

use Hafo\Fio\Mapper\PaymentMapper;
use Hafo\Fio\Payment;
use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;

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
