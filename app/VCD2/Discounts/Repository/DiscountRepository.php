<?php

namespace VCD2\Discounts\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Discounts\Discount;
use VCD2\Discounts\Mapper\DiscountMapper;

/**
 * @method DiscountMapper getMapper()
 *
 * @method Discount|NULL get($primaryKey)
 * @method Discount|NULL getBy(array $conds)
 *
 * @method Discount[]|ICollection find($ids)
 * @method Discount[]|ICollection findAll()
 * @method Discount[]|ICollection findBy(array $where)
 *
 * @method Discount hydrateEntity(array $data)
 *
 * @method Discount[]|ICollection findUsable()
*/
class DiscountRepository extends Repository {

    static function getEntityClassNames() : array {
        return [Discount::class];
    }

}
