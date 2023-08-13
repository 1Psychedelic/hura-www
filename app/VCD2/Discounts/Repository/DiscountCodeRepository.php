<?php

namespace VCD2\Discounts\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Discounts\DiscountCode;
use VCD2\Discounts\Mapper\DiscountCodeMapper;

/**
 * @method DiscountCodeMapper getMapper()
 *
 * @method DiscountCode|NULL get($primaryKey)
 * @method DiscountCode|NULL getBy(array $conds)
 *
 * @method DiscountCode[]|ICollection find($ids)
 * @method DiscountCode[]|ICollection findAll()
 * @method DiscountCode[]|ICollection findBy(array $where)
 *
 * @method DiscountCode hydrateEntity(array $data)
 *
 * @method DiscountCode[]|ICollection findUsable()
*/
class DiscountCodeRepository extends Repository {

    static function getEntityClassNames() : array {
        return [DiscountCode::class];
    }

}
