<?php

namespace VCD2\Applications\Mapper;

use Hafo\Orm\Mapper\Mapper;
use VCD2\Applications\Child;

class ApplicationAddonMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_application_addon';
    }

}
