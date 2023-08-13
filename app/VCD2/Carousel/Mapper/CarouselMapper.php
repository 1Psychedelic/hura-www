<?php

namespace VCD2\Carousel\Mapper;

use Hafo\Orm\Mapper\Mapper;

class CarouselMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_carousel';
    }

}
