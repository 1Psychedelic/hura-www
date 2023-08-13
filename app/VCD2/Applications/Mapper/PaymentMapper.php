<?php

namespace VCD2\Applications\Mapper;

use Hafo\Orm\Mapper\Mapper;

class PaymentMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_payment';
    }

}
