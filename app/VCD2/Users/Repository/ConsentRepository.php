<?php

namespace VCD2\Users\Repository;

use Hafo\Orm\Repository\Repository;
use Nette\Utils\Strings;
use Nextras\Orm\Collection\ArrayCollection;
use Nextras\Orm\Collection\ICollection;
use VCD2\Users\Consent;
use VCD2\Users\Mapper\ConsentMapper;

/**
 * @method ConsentMapper getMapper()
 *
 * @method Consent|NULL get($primaryKey)
 * @method Consent|NULL getBy(array $conds)
 *
 * @method Consent[]|ICollection find($ids)
 * @method Consent[]|ICollection findAll()
 * @method Consent[]|ICollection findBy(array $where)
 *
 * @method Consent hydrateEntity(array $data)
 */
class ConsentRepository extends Repository {

    static function getEntityClassNames() : array {
        return [Consent::class];
    }

}
