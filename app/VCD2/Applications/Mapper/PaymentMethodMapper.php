<?php

namespace VCD2\Applications\Mapper;

use Hafo\Orm\Mapper\Mapper;

class PaymentMethodMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_payment_method';
    }

    public function findSelectOptions() {
        $builder = $this->builder()
            ->select('id, name')
            ->orderBy('position ASC');
        return $this->connection->queryArgs($builder->getQuerySql(), $builder->getQueryParameters())->fetchPairs('id', 'name');
    }

}
