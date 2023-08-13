<?php

namespace VCD2\Gallery\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Applications\Invoice;
use VCD2\Carousel\Carousel;
use VCD2\Carousel\Mapper\CarouselMapper;
use VCD2\Gallery\AbstractPhoto;
use VCD2\Gallery\LostFound;
use VCD2\Gallery\Mapper\PhotoMapper;
use VCD2\Gallery\Photo;

/**
 * @method PhotoMapper getMapper()
 *
 * @method Photo|NULL get($primaryKey)
 * @method Photo|NULL getBy(array $conds)
 *
 * @method Photo[]|ICollection find($ids)
 * @method Photo[]|ICollection findAll()
 * @method Photo[]|ICollection findBy(array $where)
 *
 * @method Photo hydrateEntity(array $data)
 */
class PhotoRepository extends Repository {

    static function getEntityClassNames() : array {
        return [AbstractPhoto::class, Photo::class, LostFound::class];
    }

    public function getEntityClassName(array $data) : string {
        return AbstractPhoto::INHERITANCE_MAPPING[$data['type']];
    }

}
