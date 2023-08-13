<?php

namespace VCD2\Applications\Mapper;

use Hafo\Orm\Mapper\Mapper;

class StepChoiceMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_application_step';
    }

}
