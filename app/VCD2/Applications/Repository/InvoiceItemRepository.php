<?php

namespace VCD2\Applications\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Applications\InvoiceItem;
use VCD2\Applications\Mapper\InvoiceItemMapper;

/**
 * @method InvoiceItemMapper getMapper()
 *
 * @method InvoiceItem|NULL get($primaryKey)
 * @method InvoiceItem|NULL getBy(array $conds)
 *
 * @method InvoiceItem[]|ICollection find($ids)
 * @method InvoiceItem[]|ICollection findAll()
 * @method InvoiceItem[]|ICollection findBy(array $where)
 *
 * @method InvoiceItem hydrateEntity(array $data)
 */
class InvoiceItemRepository extends Repository {

    static function getEntityClassNames() : array {
        return [InvoiceItem::class];
    }

}
