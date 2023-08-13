<?php

namespace VCD2\Carousel\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Applications\Invoice;
use VCD2\Carousel\Carousel;
use VCD2\Carousel\Mapper\CarouselMapper;

/**
 * @method CarouselMapper getMapper()
 *
 * @method Carousel|NULL get($primaryKey)
 * @method Carousel|NULL getBy(array $conds)
 *
 * @method Carousel[]|ICollection find($ids)
 * @method Carousel[]|ICollection findAll()
 * @method Carousel[]|ICollection findBy(array $where)
 *
 * @method Carousel hydrateEntity(array $data)
 */
class CarouselRepository extends Repository {

    static function getEntityClassNames() : array {
        return [Carousel::class];
    }

}
