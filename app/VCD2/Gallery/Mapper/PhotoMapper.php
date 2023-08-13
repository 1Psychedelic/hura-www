<?php

namespace VCD2\Gallery\Mapper;

use Hafo\Orm\Mapper\Mapper;

class PhotoMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_photo';
    }

}
