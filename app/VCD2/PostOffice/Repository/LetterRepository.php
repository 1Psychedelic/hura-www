<?php

namespace VCD2\PostOffice\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\PostOffice\Letter;
use VCD2\PostOffice\Mapper\LetterMapper;

/**
 * @method LetterMapper getMapper()
 *
 * @method Letter|NULL get($primaryKey)
 * @method Letter|NULL getBy(array $conds)
 *
 * @method Letter[]|ICollection find($ids)
 * @method Letter[]|ICollection findAll()
 * @method Letter[]|ICollection findBy(array $where)
 *
 * @method Letter hydrateEntity(array $data)
 */
class LetterRepository extends Repository {

    static function getEntityClassNames() : array {
        return [Letter::class];
    }

}
