<?php

namespace VCD2\Carousel\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Carousel\AbstractCarouselPage;
use VCD2\Carousel\CarouselEventPage;
use VCD2\Carousel\CarouselPage;
use VCD2\Carousel\Mapper\CarouselPageMapper;

/**
 * @method CarouselPageMapper getMapper()
 *
 * @method CarouselPage|NULL get($primaryKey)
 * @method CarouselPage|NULL getBy(array $conds)
 *
 * @method CarouselPage[]|ICollection find($ids)
 * @method CarouselPage[]|ICollection findAll()
 * @method CarouselPage[]|ICollection findBy(array $where)
 *
 * @method CarouselPage hydrateEntity(array $data)
 */
class CarouselPageRepository extends Repository {

    static function getEntityClassNames() : array {
        return [AbstractCarouselPage::class, CarouselPage::class, CarouselEventPage::class];
    }

    public function getEntityClassName(array $data) : string {
        return AbstractCarouselPage::INHERITANCE_MAPPING[$data['type']];
    }

}
