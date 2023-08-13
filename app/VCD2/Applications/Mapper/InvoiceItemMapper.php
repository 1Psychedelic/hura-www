<?php

namespace VCD2\Applications\Mapper;

use Hafo\Orm\Mapper\Mapper;

class InvoiceItemMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_invoice_item';
    }

}
