<?php

namespace VCD2\Ebooks\Repository;

use Hafo\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;
use VCD2\Ebooks\Ebook;
use VCD2\Ebooks\Mapper\EbookMapper;

/**
 * @method EbookMapper getMapper()
 *
 * @method Ebook|NULL get($primaryKey)
 * @method Ebook|NULL getBy(array $conds)
 *
 * @method Ebook[]|ICollection find($ids)
 * @method Ebook[]|ICollection findAll()
 * @method Ebook[]|ICollection findBy(array $where)
 *
 * @method Ebook hydrateEntity(array $data)
*/
class EbookRepository extends Repository {

    static function getEntityClassNames() : array {
        return [Ebook::class];
    }

}
