<?php

namespace VCD2\Emails\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Emails\Email;
use VCD2\Emails\Mapper\EmailMapper;

/**
* @method EmailMapper getMapper()
*
* @method Email|NULL get($primaryKey)
* @method Email|NULL getBy(array $conds)
*
* @method Email[]|ICollection find($ids)
* @method Email[]|ICollection findAll()
* @method Email[]|ICollection findBy(array $where)
*
* @method Email hydrateEntity(array $data)
*/
class EmailRepository extends Repository {

    static function getEntityClassNames() : array {
        return [Email::class];
    }

}
