<?php

namespace VCD2\Credits\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Credits\Credit;
use VCD2\Credits\Mapper\CreditMapper;

/**
 * @method CreditMapper getMapper()
 *
 * @method Credit|NULL get($primaryKey)
 * @method Credit|NULL getBy(array $conds)
 *
 * @method Credit[]|ICollection find($ids)
 * @method Credit[]|ICollection findAll()
 * @method Credit[]|ICollection findBy(array $where)
 *
 * @method Credit hydrateEntity(array $data)
 *
 * @method int getCirculatingValue()
*/
class CreditRepository extends Repository {

    static function getEntityClassNames() : array {
        return [Credit::class];
    }

}
