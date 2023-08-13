<?php
declare(strict_types=1);

namespace VCD2\FacebookImages\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\FacebookImages\FacebookImage;
use VCD2\FacebookImages\Mapper\FacebookImageMapper;

/**
 * @method FacebookImageMapper getMapper()
 *
 * @method FacebookImage|NULL get($primaryKey)
 * @method FacebookImage|NULL getBy(array $conds)
 *
 * @method FacebookImage[]|ICollection find($ids)
 * @method FacebookImage[]|ICollection findAll()
 * @method FacebookImage[]|ICollection findBy(array $where)
 *
 * @method FacebookImage hydrateEntity(array $data)
 */
class FacebookImageRepository extends Repository
{
    public static function getEntityClassNames(): array
    {
        return [FacebookImage::class];
    }
}
