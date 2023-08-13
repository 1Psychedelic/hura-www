<?php

namespace VCD2\Emails\Mapper;

use Hafo\Orm\Mapper\Mapper;

class EmailMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_email';
    }

}
