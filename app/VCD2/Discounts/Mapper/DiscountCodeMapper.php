<?php

namespace VCD2\Discounts\Mapper;

use Hafo\Orm\Mapper\Mapper;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Mapper\Dbal\DbalMapper;
use Nextras\Orm\Mapper\IMapper;
use VCD2\Events\Event;
use VCD2\Events\Mapper\EventMapper;

class DiscountCodeMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_discount_code';
    }

    public function getManyHasManyParameters(PropertyMetadata $sourceProperty, DbalMapper $targetMapper) {
        switch(TRUE) {
            case $targetMapper instanceof EventMapper:
                return ['vcd_discount_event', ['discount', 'event']];
                break;
        }
        return parent::getManyHasManyParameters($sourceProperty, $targetMapper);
    }

    public function findUsable() {
        $builder = $this->builder()
            ->leftJoin('vcd_discount_code', 'vcd_discount_event', 'de', 'vcd_discount_code.id = de.discount')
            ->leftJoin('de', 'vcd_event', 'e', 'de.event = e.id')
            ->where('vcd_discount_code.usages_left IS NULL OR vcd_discount_code.usages_left > 0')
            ->andWhere('vcd_discount_code.expires IS NULL OR vcd_discount_code.expires > NOW()')
            ->andWhere('e.applicable_until > NOW() OR e.id IS NULL')
            ->groupBy('vcd_discount_code.id')
            ->orderBy('vcd_discount_code.id DESC');
        return $this->toCollection($builder);
    }

}
