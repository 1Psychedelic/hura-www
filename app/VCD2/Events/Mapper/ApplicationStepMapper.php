<?php

namespace VCD2\Events\Mapper;

use Hafo\Orm\Mapper\Mapper;

class ApplicationStepMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_event_step';
    }

}
