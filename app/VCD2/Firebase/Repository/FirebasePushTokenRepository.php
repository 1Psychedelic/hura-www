<?php

namespace VCD2\Firebase\Repository;

use Hafo\Orm\Repository\Repository;
use Nette\Utils\Strings;
use Nextras\Orm\Collection\ArrayCollection;
use Nextras\Orm\Collection\ICollection;
use VCD2\Firebase\FirebasePushToken;
use VCD2\Firebase\Mapper\FirebasePushTokenMapper;

/**
 * @method FirebasePushTokenMapper getMapper()
 *
 * @method FirebasePushToken|NULL get($primaryKey)
 * @method FirebasePushToken|NULL getBy(array $conds)
 *
 * @method FirebasePushToken[]|ICollection find($ids)
 * @method FirebasePushToken[]|ICollection findAll()
 * @method FirebasePushToken[]|ICollection findBy(array $where)
 *
 * @method FirebasePushToken hydrateEntity(array $data)
 */
class FirebasePushTokenRepository extends Repository {

    static function getEntityClassNames() : array {
        return [FirebasePushToken::class];
    }
}
