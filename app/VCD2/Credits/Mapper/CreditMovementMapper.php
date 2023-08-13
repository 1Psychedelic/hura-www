<?php

namespace VCD2\Credits\Mapper;

use Hafo\Orm\Mapper\Mapper;
use Nextras\Dbal\QueryBuilder\QueryBuilder;

class CreditMovementMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_credit_movement';
    }

    public function builder(): QueryBuilder
    {
        $builder = parent::builder();
        $builder->orderBy('id DESC');
        return $builder;
    }

}
