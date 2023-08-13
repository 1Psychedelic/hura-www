<?php

namespace VCD2\Applications\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Applications\Mapper\PaymentMethodMapper;
use VCD2\Applications\PaymentMethod;

/**
 * @method PaymentMethodMapper getMapper()
 *
 * @method PaymentMethod|NULL get($primaryKey)
 * @method PaymentMethod|NULL getBy(array $conds)
 *
 * @method PaymentMethod[]|ICollection find($ids)
 * @method PaymentMethod[]|ICollection findAll()
 * @method PaymentMethod[]|ICollection findBy(array $where)
 *
 * @method PaymentMethod hydrateEntity(array $data)
 *
 * @method string[] findSelectOptions()
 */
class PaymentMethodRepository extends Repository {

    static function getEntityClassNames() : array {
        return [PaymentMethod::class];
    }

    /** @return PaymentMethod[]|ICollection */
    function findEnabled() {
        return $this->findBy(['isEnabled' => TRUE])->orderBy('position', ICollection::ASC);
    }

}
