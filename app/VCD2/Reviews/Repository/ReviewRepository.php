<?php
declare(strict_types=1);

namespace VCD2\Reviews\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Reviews\Mapper\ReviewMapper;
use VCD2\Reviews\Review;

/**
 * @method ReviewMapper getMapper()
 *
 * @method Review|NULL get($primaryKey)
 * @method Review|NULL getBy(array $conds)
 *
 * @method Review[]|ICollection find($ids)
 * @method Review[]|ICollection findAll()
 * @method Review[]|ICollection findBy(array $where)
 *
 * @method Review hydrateEntity(array $data)
 *
 * @method Review[]|ICollection findRandom()
 */
class ReviewRepository extends Repository
{
    public static function getEntityClassNames(): array
    {
        return [Review::class];
    }
}
