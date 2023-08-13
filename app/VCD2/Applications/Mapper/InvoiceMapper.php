<?php

namespace VCD2\Applications\Mapper;

use Hafo\Orm\Mapper\Mapper;
use VCD2\Applications\Repository\InvoiceRepository;

/**
 * @see InvoiceRepository
 */
class InvoiceMapper extends Mapper {

    protected $encrypted = [
        'encryptedName',
        'encryptedCity',
        'encryptedStreet',
        'encryptedZip',
        'encryptedIco',
        'encryptedDic',
    ];

    public function getTableName() : string {
        return 'vcd_invoice';
    }

    public function getCountForYear($year) {
        $builder = $this->builder()
            ->select('MAX(count_this_year)')
            ->where('YEAR(created_at) = %i', $year);
        return (int)$this->connection->queryArgs($builder->getQuerySql(), $builder->getQueryParameters())->fetchField();
    }

}
