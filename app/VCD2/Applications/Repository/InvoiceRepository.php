<?php

namespace VCD2\Applications\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Applications\Invoice;
use VCD2\Applications\Mapper\InvoiceMapper;

/**
 * @method InvoiceMapper getMapper()
 *
 * @method Invoice|NULL get($primaryKey)
 * @method Invoice|NULL getBy(array $conds)
 *
 * @method Invoice[]|ICollection find($ids)
 * @method Invoice[]|ICollection findAll()
 * @method Invoice[]|ICollection findBy(array $where)
 *
 * @method Invoice hydrateEntity(array $data)
 *
 * @method int getCountForYear($year)
 *
 * @see InvoiceMapper
 */
class InvoiceRepository extends Repository {

    static function getEntityClassNames() : array {
        return [Invoice::class];
    }

    /**
     * @param $invoiceId
     * @return NULL|Invoice
     */
    function getByInvoiceId($invoiceId) {
        return $this->getBy(['invoiceId' => $invoiceId]);
    }

}
