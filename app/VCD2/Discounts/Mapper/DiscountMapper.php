<?php

namespace VCD2\Discounts\Mapper;

use Hafo\Orm\Mapper\Mapper;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Mapper\Dbal\DbalMapper;
use Nextras\Orm\Mapper\IMapper;
use VCD2\Events\Event;
use VCD2\Events\Mapper\EventMapper;

class DiscountMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_discount';
    }

}
