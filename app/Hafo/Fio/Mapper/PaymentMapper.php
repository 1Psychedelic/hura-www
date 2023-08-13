<?php

namespace Hafo\Fio\Mapper;

use Hafo\Orm\Mapper\Mapper;

class PaymentMapper extends Mapper {

    public function getTableName() : string {
        return 'fio_payment';
    }

}
