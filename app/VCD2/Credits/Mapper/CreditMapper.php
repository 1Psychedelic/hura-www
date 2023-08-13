<?php

namespace VCD2\Credits\Mapper;

use Hafo\Orm\Mapper\Mapper;
use Nextras\Dbal\QueryBuilder\QueryBuilder;

class CreditMapper extends Mapper {

    public function getTableName() : string {
        return 'vcd_credit';
    }

    public function getCirculatingValue() {
        return $this->connection->query('
            SELECT SUM(c.amount) FROM vcd_credit c
            WHERE expires_at > NOW() OR expires_at IS NULL
        ')->fetchField();
    }

}
