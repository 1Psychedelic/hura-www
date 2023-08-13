<?php

namespace Hafo\GoPay\Mapper;

use Hafo\Orm\Mapper\Mapper;

class PaymentMapper extends Mapper {

    public function getTableName() : string {
        return 'gopay_payment';
    }

}
