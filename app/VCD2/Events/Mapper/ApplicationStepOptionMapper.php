<?php

namespace VCD2\Events\Mapper;

use Hafo\Orm\Mapper\Mapper;

class ApplicationStepOptionMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_event_step_option';
    }

}
