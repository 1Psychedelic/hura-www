<?php

namespace VCD2\PostOffice\Mapper;

use Hafo\Orm\Mapper\Mapper;

class LetterMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_letter';
    }

}
