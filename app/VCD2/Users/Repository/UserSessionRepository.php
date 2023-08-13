<?php

namespace VCD2\Users\Repository;

use Hafo\Orm\Repository\Repository;
use Nette\Utils\Strings;
use Nextras\Orm\Collection\ArrayCollection;
use Nextras\Orm\Collection\ICollection;
use VCD2\Users\UserSession;
use VCD2\Users\Mapper\UserSessionMapper;

/**
 * @method UserSessionMapper getMapper()
 *
 * @method UserSession|NULL get($primaryKey)
 * @method UserSession|NULL getBy(array $conds)
 *
 * @method UserSession[]|ICollection find($ids)
 * @method UserSession[]|ICollection findAll()
 * @method UserSession[]|ICollection findBy(array $where)
 *
 * @method UserSession hydrateEntity(array $data)
 */
class UserSessionRepository extends Repository {

    static function getEntityClassNames() : array {
        return [UserSession::class];
    }

}
